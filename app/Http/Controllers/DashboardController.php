<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Facilty;
use App\Models\Flight;
use App\Models\SiteSection;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Halaman Utama Publik
     * 
     * Menampilkan halaman utama publik dengan form pencarian penerbangan dan bagian promosi/informasi situs.
     * 
     * @group Halaman Utama
     * @response 200 (HTML View) Halaman landing page
     */
    public function landing()
    {
        /* Ambil daftar seluruh bandara berurutan secara abjad nama kota */
        $airports = Airport::orderBy('city', 'asc')->get();

        /* Ambil seluruh konfigurasi site section aktif untuk halaman depan */
        $sections = SiteSection::active()->get()->keyBy('slug');

        return view('welcome', [
            'airports' => $airports,
            'sections' => $sections,
        ]);
    }

    /**
     * Cari Bandara
     * 
     * Endpoint untuk mencari bandara berdasarkan nama kota, nama bandara, atau kode IATA.
     * 
     * @group Pencarian
     * @queryParam search string Kata kunci pencarian bandara. Example: Soekarno
     * 
     * @response 200 [
     *   {
     *     "id": 1,
     *     "iata_code": "CGK",
     *     "name": "Soekarno-Hatta International Airport",
     *     "city": "Jakarta",
     *     "country": "Indonesia"
     *   }
     * ]
     */
    public function getAirports(Request $request)
    {
        /* Ambil kata kunci pencarian bandara */
        $search = $request->query('search');
        $query = Airport::query();

        /* Jika ada kata kunci, lakukan pencarian berdasarkan kota, nama bandara, atau kode IATA */
        if ($search) {
            $query->where('city', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhere('iata_code', 'like', '%' . $search . '%');
        }
        return response()->json($query->limit(10)->get());
    }

    /**
     * Dashboard Pengguna
     * 
     * Menampilkan halaman dashboard untuk pengguna yang sudah login untuk mencari penerbangan.
     * 
     * @group Dashboard Pelanggan
     * @queryParam departure_id int ID bandara keberangkatan. Example: 1
     * @queryParam arrival_id int ID bandara tujuan. Example: 2
     * @queryParam date string Tanggal keberangkatan (format Y-m-d). Example: 2026-06-26
     * @queryParam passengers int Jumlah penumpang (1-9). Example: 1
     */
    public function index(Request $request)
    {
        /* Ambil data bandara untuk dropdown form pencarian */
        $airports = Airport::orderBy('city', 'asc')->get();

        return view('dashboard', [
            'airports' => $airports,
            'search' => $request->only(
                'departure_id',
                'arrival_id',
                'date',
                'passengers'
            ),
        ]);
    }

    /**
     * Cari Penerbangan
     * 
     * Mencari penerbangan yang tersedia berdasarkan bandara keberangkatan, tujuan, tanggal, dan filter lainnya.
     * 
     * @group Pencarian
     * @queryParam departure_id int ID bandara keberangkatan. Example: 1
     * @queryParam arrival_id int ID bandara tujuan. Example: 2
     * @queryParam date string Tanggal keberangkatan (format Y-m-d). Example: 2026-06-26
     * @queryParam passengers int Jumlah kursi/penumpang yang dipesan. Example: 1
     * @queryParam airline_id int[] ID maskapai untuk filter (opsional). Example: [1]
     * @queryParam facility_id int[] ID fasilitas untuk filter (opsional). Example: [1, 2]
     * @queryParam transit_type string[] Tipe transit: direct, transit_1x, transit_2x (opsional). Example: ["direct"]
     */
    public function flights(Request $request)
    {
        /* Muat daftar bandara, maskapai, dan fasilitas untuk widget filter sidebar */
        $airports   = Airport::orderBy('city', 'asc')->get();
        $airlines   = Airline::orderBy('name', 'asc')->get();
        $facilities = Facilty::orderBy('name', 'asc')->get();

        /* Query dasar — selalu load semua penerbangan beserta segmen, kelas, dan fasilitasnya */
        $query = Flight::with([
            'airline',
            'segments' => function ($q) {
                $q->orderBy('sequence', 'asc');
            },
            'segments.airport',
            'classes.facilties',
        ]);

        /* Filter: segment pertama harus berasal dari bandara keberangkatan dan sesuai tanggal */
        if ($request->filled('departure_id')) {
            $departureId = $request->input('departure_id');
            $date        = $request->input('date');

            $query->whereHas('segments', function ($q) use ($departureId, $date) {
                $q->where('airport_id', $departureId)
                  ->where('sequence', 1);

                if ($date) {
                    $q->whereDate('time', $date);
                }
            });
        }

        // Filter: salah satu segment menuju arrival
        // Sekarang independen — tetap jalan walau departure_id kosong
        if ($request->filled('arrival_id')) {
            $arrivalId = $request->input('arrival_id');

            $query->whereHas('segments', function ($q) use ($arrivalId) {
                $q->where('airport_id', $arrivalId)
                  ->where('sequence', '>', 1);
            });
        }

        // Filter airline
        if ($request->filled('airline_id')) {
            $query->whereIn(
                'airline_id',
                (array) $request->input('airline_id')
            );
        }

        // Filter facility
        if ($request->filled('facility_id')) {
            $facilityIds = (array) $request->input('facility_id');

            $query->whereHas('classes.facilties', function ($q) use ($facilityIds) {
                $q->whereIn('facilties.id', $facilityIds);
            });
        }

        $flights = $query->get();

        // ===== Validasi arrival = segment TERAKHIR (jalan selama arrival_id diisi) =====
        if ($request->filled('arrival_id')) {
            $arrivalId = $request->input('arrival_id');

            $flights = $flights->filter(function ($flight) use ($arrivalId) {
                $lastSegment = $flight->segments->last();
                return $lastSegment && (int) $lastSegment->airport_id === (int) $arrivalId;
            });
        }

        // ===== Filter berdasarkan tipe transit =====
        if ($request->filled('transit_type')) {
            $transitTypes = (array) $request->input('transit_type');

            $flights = $flights->filter(function ($flight) use ($transitTypes) {
                $segmentCount = $flight->segments->count();
                $transitCount = max($segmentCount - 2, 0);

                if ($transitCount === 0 && in_array('direct', $transitTypes)) {
                    return true;
                }

                if ($transitCount === 1 && in_array('transit_1x', $transitTypes)) {
                    return true;
                }

                if ($transitCount >= 2 && in_array('transit_2x', $transitTypes)) {
                    return true;
                }

                return false;
            });
        }

        $flights = $flights->values();

        return view('flights', [
            'airports'   => $airports,
            'airlines'   => $airlines,
            'facilities' => $facilities,
            'flights'    => $flights,
            'search' => $request->only(
                'departure_id',
                'arrival_id',
                'date',
                'passengers'
            ),
        ]);
    }

    /**
     * Pilih Kelas Penerbangan
     * 
     * Menampilkan pilihan kelas penerbangan (Economy, Business, dll.) beserta harga dan fasilitas masing-masing.
     * 
     * @group Pemesanan Tiket
     * @urlParam flight string required Nomor penerbangan. Example: GA-401
     * @queryParam passengers int Jumlah penumpang. Example: 1
     */
    public function chooseTier(Flight $flight, Request $request)
    {
        $flight->load([
            'airline',
            'segments' => function ($q) {
                $q->orderBy('sequence', 'asc');
            },
            'segments.airport',
            'classes.facilties',
        ]);

        return view('flights.choose-tier', [
            'flight'     => $flight,
            'passengers' => $request->input('passengers', 1),
        ]);
    }
}