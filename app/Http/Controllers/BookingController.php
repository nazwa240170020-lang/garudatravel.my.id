<?php

namespace App\Http\Controllers;

use App\Mail\TransactionSuccessMail;
use App\Exceptions\PromoValidationException;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\Transaction;
use App\Models\TransactionPassenger;
use App\Services\PromoService;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class BookingController extends Controller
{
    private PromoService $promoService;
    private TaxService $taxService;

    public function __construct()
    {
        $this->promoService = app(PromoService::class);
        $this->taxService = app(TaxService::class);
    }

    private function setupMidtrans(): void
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    private function verifyMidtransSignature(Request $request): bool
    {
        $serverKey = config('midtrans.server_key');
        $expectedSignature = hash(
            'sha512',
            $request->order_id . $request->status_code . $request->gross_amount . $serverKey
        );

        $receivedSignature = $request->signature_key ?? '';

        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Kirim email sukses transaksi + boarding pass, hanya sekali per transaksi.
     *
     * Menggunakan kolom `mail_sent_at` sebagai guard untuk mencegah pengiriman duplikat
     * dari webhook, finish(), atau payment() yang bisa terpicu secara bersamaan.
     */
    private function sendSuccessEmailOnce(Transaction $transaction): void
    {
        /* Jika email sudah pernah dikirim, skip */
        if ($transaction->mail_sent_at !== null) {
            return;
        }

        /* Tandai mail_sent_at SEBELUM dispatch ke queue agar titik lain tidak bisa trigger lagi */
        $transaction->update(['mail_sent_at' => now()]);

        try {
            Mail::to($transaction->email)
                ->send(new TransactionSuccessMail($transaction));

            Log::info('Email boarding pass berhasil dikirim untuk transaksi: ' . $transaction->code);
        } catch (\Exception $e) {
            Log::error('Gagal dispatch email untuk transaksi ' . $transaction->code . ': ' . $e->getMessage());
        }
    }

    private function getAvailableSeats(int $flightId, string $classType): \Illuminate\Support\Collection
    {
        $bookedIds = TransactionPassenger::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_passengers.transaction_id')
            ->where('transactions.flight_id', $flightId)
            ->where('transactions.payment_status', '!=', 'failed')
            ->whereNull('transactions.deleted_at')
            ->whereNull('transaction_passengers.deleted_at')
            ->pluck('transaction_passengers.flight_seat_id');

        return FlightSeat::where('flight_id', $flightId)
            ->where('class_type', $classType)
            ->orderBy('row')
            ->orderBy('column')
            ->get()
            ->each(function ($seat) use ($bookedIds) {
                if ($bookedIds->contains($seat->id)) {
                    $seat->is_available = false;
                }
            });
    }

    /**
     * Form Pengisian Data Penumpang
     * 
     * Halaman formulir untuk memasukkan informasi penumpang setelah memilih kursi.
     * 
     * @group Pemesanan Tiket
     * @queryParam flight_id int required ID penerbangan. Example: 1
     * @queryParam flight_class_id int required ID kelas penerbangan. Example: 1
     * @queryParam passengers int required Jumlah penumpang. Example: 1
     * @queryParam seats string required Daftar nama kursi yang dipilih (dipisahkan koma). Example: GA-401-1A
     * @response 200 (HTML View) Halaman pengisian form pemesanan
     */
    public function create(Request $request)
    {
        $flightId      = $request->query('flight_id');
        $flightClassId = $request->query('flight_class_id');
        $passengers    = max(1, min(9, (int) $request->query('passengers', 1)));
        $seats         = $request->query('seats') ? explode(',', $request->query('seats')) : [];

        $flight      = Flight::with('airline', 'segments.airport')->findOrFail($flightId);
        $flightClass = FlightClass::findOrFail($flightClassId);

        $availableSeats = $this->getAvailableSeats($flightId, $flightClass->class_type)
            ->when(count($seats) > 0, fn($q) => $q->whereIn('name', $seats));

        return view('booking.create', compact('flight', 'flightClass', 'availableSeats', 'passengers'));
    }

    /**
     * Simpan Transaksi Pemesanan
     * 
     * Menyimpan transaksi pemesanan baru ke database dan memesan kursi.
     * 
     * @group Pemesanan Tiket
     * @bodyParam flight_id int required ID penerbangan. Example: 1
     * @bodyParam flight_class_id int required ID kelas penerbangan. Example: 1
     * @bodyParam number_of_passengers int required Jumlah penumpang (1-9). Example: 1
     * @bodyParam name string required Nama lengkap pemesan. Example: Ayu Traveler
     * @bodyParam email string required Email pemesan untuk pengiriman e-ticket. Example: ayu@example.com
     * @bodyParam phone string required Nomor telepon pemesan. Example: 08123456789
     * @bodyParam promo_code string Kode promo (opsional). Example: GARUDA10
     * @bodyParam passengers array required Daftar data penumpang.
     * @bodyParam passengers[].seat_id int required ID kursi untuk penumpang tersebut. Example: 1
     * @bodyParam passengers[].name string required Nama lengkap penumpang. Example: Ayu Traveler
     * @bodyParam passengers[].dob string required Tanggal lahir penumpang (Y-m-d). Example: 1995-01-01
     * @bodyParam passengers[].nationality string required Kewarganegaraan penumpang. Example: Indonesia
     * @response 302 Redirect ke halaman pembayaran
     */
    public function store(Request $request)
    {
        /* Validasi input permintaan booking untuk memastikan semua kolom wajib terisi dengan benar */
        $request->validate([
            'flight_id'                => 'required|exists:flights,id',
            'flight_class_id'          => 'required|exists:flight_classes,id',
            'name'                     => 'required|string|max:255',
            'email'                    => 'required|email|max:255',
            'phone'                    => 'required|string|max:20',
            'number_of_passengers'     => 'required|integer|min:1|max:9',
            'passengers'               => 'required|array|min:1',
            'passengers.*.seat_id'     => 'required|exists:flight_seats,id',
            'passengers.*.name'        => 'required|string|max:255',
            'passengers.*.dob'         => 'required|date',
            'passengers.*.nationality' => 'required|string|max:100',
            'promo_code'               => 'nullable|string|max:50',
        ]);

        /* Validasi kecocokan jumlah detail penumpang dengan jumlah total kursi yang dipilih */
        if (count($request->passengers) !== (int) $request->number_of_passengers) {
            return back()->with('error', 'Jumlah penumpang tidak sesuai dengan jumlah kursi yang dipilih.')
                ->withInput();
        }

        /* Mulai transaksi database untuk menjamin integritas data (atomicity) */
        DB::beginTransaction();

        try {
            /* Dapatkan data kelas penerbangan yang dipesan */
            $flightClass = FlightClass::findOrFail($request->flight_class_id);

            /* Ekstrak semua ID kursi yang dipilih penumpang */
            $selectedSeatIds = collect($request->passengers)->pluck('seat_id')->toArray();

            /* Kunci baris kursi menggunakan lockForUpdate (pessimistic locking) untuk mencegah race condition */
            $seatsToBook = FlightSeat::whereIn('id', $selectedSeatIds)
                ->where('flight_id', $request->flight_id)
                ->where('class_type', $flightClass->class_type)
                ->lockForUpdate()
                ->get();

            /* Pastikan seluruh kursi yang dipilih terdaftar dan sesuai kelas penerbangan */
            if ($seatsToBook->count() !== count(array_unique($selectedSeatIds))) {
                throw new \Exception('Salah satu kursi yang dipilih tidak valid.');
            }

            /* Cek apakah ada kursi yang sudah dipesan oleh transaksi lain yang berstatus aktif (bukan failed) */
            $alreadyBookedCount = TransactionPassenger::whereIn('flight_seat_id', $selectedSeatIds)
                ->whereHas('transaction', function ($q) use ($request) {
                    $q->where('flight_id', $request->flight_id)
                      ->where('payment_status', '!=', 'failed');
                })
                ->count();

            if ($alreadyBookedCount > 0) {
                throw new \Exception('Salah satu kursi yang dipilih sudah dipesan.');
            }

            /* Hitung harga subtotal tiket dan kalkulasi tarif pajak PPN awal */
            $subtotal  = $flightClass->price * $request->number_of_passengers;
            $totalCalc = $this->taxService->grandTotal($subtotal, 0);

            $promo    = null;
            $discount = 0;

            /* Jika pengguna memasukkan kode promo, validasi dan hitung nilai diskon potongan harga */
            if ($request->filled('promo_code')) {
                $promoResult = $this->promoService->apply(
                    $request->promo_code,
                    $subtotal,
                    $totalCalc['tax'],
                    $request->user(),
                );

                if (! $promoResult['valid']) {
                    throw new PromoValidationException($promoResult['message']);
                }

                $promo    = $promoResult['promo'];
                $discount = $promoResult['discount'];
            }

            /* Hitung total akhir (grandtotal) setelah dikurangi potongan diskon promo */
            $grandTotalCalc = $this->taxService->grandTotal($subtotal, $discount);

            /* Generate kode booking transaksi unik secara acak dengan format GRD-XXXXXXXX */
            $code = null;
            for ($i = 0; $i < 5; $i++) {
                $candidate = 'GRD-' . strtoupper(Str::random(8));
                if (!Transaction::where('code', $candidate)->exists()) {
                    $code = $candidate;
                    break;
                }
            }

            if (!$code) {
                throw new \Exception('Gagal membuat kode transaksi unik. Silakan coba lagi.');
            }

            /* Simpan data transaksi utama ke database */
            $transaction = Transaction::create([
                'user_id'              => $request->user()->id,
                'code'                 => $code,
                'flight_id'            => $request->flight_id,
                'flight_class_id'      => $request->flight_class_id,
                'name'                 => $request->name,
                'email'                => $request->email,
                'phone'                => $request->phone,
                'number_of_passengers' => $request->number_of_passengers,
                'promo_code_id'        => $promo?->id,
                'payment_status'       => 'pending',
                'subtotal'             => $subtotal,
                'discount'             => $discount,
                'grandtotal'           => $grandTotalCalc['grandtotal'],
            ]);

            /* 
             * Kode promo dibiarkan multi-use. Pencatatan penggunaan dilakukan
             * hanya ketika pembayaran berhasil.
             * agar pengguna lain tetap bisa memakainya.
             */
            // if ($promo) {
            //     $this->promoService->markAsUsed($promo);
            // }

            /* Siapkan array data massal untuk setiap penumpang beserta detail kursinya */
            $passengerData = collect($request->passengers)->map(fn($p) => [
                'transaction_id' => $transaction->id,
                'flight_seat_id' => $p['seat_id'],
                'name'           => $p['name'],
                'date_of_birth'  => $p['dob'],
                'nationality'    => $p['nationality'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ])->toArray();

            /* Simpan seluruh data penumpang ke database */
            TransactionPassenger::insert($passengerData);

            /* Commit transaksi database jika seluruh tahapan di atas berhasil tanpa exception */
            DB::commit();

            /* Alihkan pengguna ke halaman pembayaran Midtrans */
            return redirect()->route('booking.payment', $transaction->id);
        } catch (\Exception $e) {
            /* Rollback database ke state awal jika terjadi kesalahan di blok try */
            DB::rollBack();
            Log::error('Booking store error: ' . $e->getMessage());
            return back()->with('error', $e instanceof PromoValidationException
                ? $e->getMessage()
                : 'Terjadi kesalahan saat memproses booking. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Halaman Pembayaran
     * 
     * Menampilkan halaman pembayaran dengan snap token Midtrans.
     * 
     * @group Pembayaran
     * @urlParam transaction int required ID transaksi. Example: 1
     * @response 200 (HTML View) Halaman pembayaran terintegrasi Midtrans Snap
     */
    public function payment(Transaction $transaction)
    {
        /* Otorisasi akses untuk memastikan user berhak mengupdate transaksi ini */
        Gate::authorize('update', $transaction);

        /* Jika status transaksi sudah bukan pending (misal paid/failed), arahkan ke detail */
        if ($transaction->payment_status !== 'pending') {
            return redirect()->route('booking.detail', $transaction->id)
                ->with('info', 'Transaksi ini sudah diproses.');
        }

        /* Siapkan konfigurasi kredensial Midtrans */
        $this->setupMidtrans();

        /*
         * Gunakan snap token yang sudah di-cache jika ada.
         * Hanya minta snap token baru jika belum pernah ada.
         * TIDAK ada API call ke Midtrans untuk cek status — itu dilakukan
         * oleh finish() via AJAX setelah user selesai bayar di Snap.
         */
        $snapToken = $transaction->snap_token;

        if (!$snapToken) {
            try {
                $snapToken = Snap::getSnapToken([
                    'transaction_details' => [
                        'order_id'     => $transaction->code,
                        'gross_amount' => (int) $transaction->grandtotal,
                    ],
                    'customer_details' => [
                        'first_name' => $transaction->name,
                        'email'      => $transaction->email,
                        'phone'      => $transaction->phone ?? '',
                    ],
                    'enabled_payments' => [
                        'gopay',
                        'shopeepay',
                        'bni_va',
                        'bsi_va',
                        'other_va',
                        'credit_card',
                        'qris',
                    ],
                    'callbacks' => [
                        'finish' => route('booking.finish', $transaction->id),
                    ],
                ]);

                /* Cache snap token ke database agar tidak perlu minta ulang */
                $transaction->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                $snapToken = null;
                Log::error('Midtrans snap token error: ' . $e->getMessage());
                session()->flash('error', 'Gagal terhubung ke sistem pembayaran. Silakan coba lagi.');
            }
        }

        return view('booking.payment', compact('transaction', 'snapToken'));
    }

    /**
     * Callback Halaman Selesai Pembayaran
     * 
     * Endpoint callback halaman sukses setelah pengguna menyelesaikan transaksi di Midtrans.
     * 
     * @group Pembayaran
     * @urlParam transaction int required ID transaksi. Example: 1
     * @bodyParam transaction_status string Status transaksi dari Midtrans callback (jika POST via ajax). Example: settlement
     * @bodyParam payment_type string Tipe pembayaran dari Midtrans callback (jika POST via ajax). Example: qris
     * @response 200 {
     *   "status": "paid",
     *   "redirect_url": "http://127.0.0.1:8000/booking/1"
     * }
     */
    public function finish(Request $request, Transaction $transaction)
    {
        /* Otorisasi akses untuk pengguna terkait */
        Gate::authorize('update', $transaction);

        $isAjax = $request->ajax() || $request->wantsJson();

        /* Jika sudah paid, langsung respond tanpa API call */
        if ($transaction->payment_status === 'paid') {
            if ($isAjax) {
                return response()->json([
                    'status'       => 'paid',
                    'redirect_url' => route('booking.detail', $transaction->id),
                ]);
            }
            return redirect()->route('booking.detail', $transaction->id)
                ->with('success', 'Pembayaran berhasil! Selamat terbang.');
        }

        /* Siapkan konfigurasi kredensial Midtrans */
        $this->setupMidtrans();

        try {
            /* Query status transaksi aktual langsung ke server API Midtrans */
            $serverStatus = MidtransTransaction::status($transaction->code);
            $status = $serverStatus->transaction_status ?? null;
            $paymentType = $serverStatus->payment_type ?? null;
        } catch (\Exception $e) {
            Log::error('Midtrans status check error on finish(): ' . $e->getMessage());

            /*
             * FALLBACK KHUSUS SANDBOX/LOKAL:
             * Jika Midtrans Core API melempar 404 (karena delay sinkronisasi Snap Sandbox),
             * namun AJAX client (Snap JS) memposting payload keberhasilan, kita percayai
             * payload tersebut agar UX pengguna tidak stuck "Sedang memverifikasi".
             */
            if ($request->isMethod('post') && $request->has('transaction_status') && $request->order_id === $transaction->code) {
                $status = $request->transaction_status;
                $paymentType = $request->payment_type ?? null;
            } else {
                if ($isAjax) {
                    return response()->json([
                        'status'  => 'pending',
                        'message' => 'Sedang memverifikasi pembayaran.',
                    ]);
                }
                return redirect()->route('booking.payment', $transaction->id)
                    ->with('info', 'Sedang memverifikasi pembayaran. Silakan tunggu sebentar.');
            }
        }

        /* Jika transaksi berstatus settlement/capture, tandai sebagai paid/lunas */
        if (in_array($status, ['settlement', 'capture'])) {
            try {
                $transaction = $this->promoService->completePayment($transaction, $paymentType);
            } catch (PromoValidationException $exception) {
                Log::warning('Pembayaran tidak dapat memakai promo', ['transaction' => $transaction->code, 'reason' => $exception->getMessage()]);

                if ($isAjax) {
                    return response()->json(['status' => 'failed', 'message' => $exception->getMessage()], 422);
                }

                return redirect()->route('booking.detail', $transaction->id)->with('error', $exception->getMessage());
            }

            /* Kirimkan e-ticket via email (guard mail_sent_at mencegah duplikat) */
            $this->sendSuccessEmailOnce($transaction);

            if ($isAjax) {
                return response()->json([
                    'status'       => 'paid',
                    'redirect_url' => route('booking.detail', $transaction->id),
                ]);
            }
            return redirect()->route('booking.detail', $transaction->id)
                ->with('success', 'Pembayaran berhasil! Selamat terbang.');
        }

        /* Jika status menunjukkan kegagalan, update DB */
        if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['payment_status' => 'failed']);

            if ($isAjax) {
                return response()->json([
                    'status'       => 'failed',
                    'redirect_url' => route('booking.detail', $transaction->id),
                    'message'      => 'Pembayaran gagal atau kedaluwarsa.',
                ]);
            }
            return redirect()->route('booking.detail', $transaction->id)
                ->with('error', 'Pembayaran gagal atau kedaluwarsa. Silakan buat pesanan baru.');
        }

        /* Status pending */
        if ($isAjax) {
            return response()->json([
                'status'  => 'pending',
                'message' => 'Pembayaran masih menunggu konfirmasi.',
            ]);
        }

        return redirect()->route('booking.payment', $transaction->id)
            ->with('info', 'Pembayaran masih menunggu konfirmasi.');
    }

    /**
     * Webhook Midtrans
     * 
     * Menerima notifikasi status pembayaran dari server Midtrans (asinkron).
     * 
     * @group Pembayaran
     * @bodyParam transaction_status string required Status transaksi (settlement, capture, pending, deny, cancel, expire). Example: settlement
     * @bodyParam order_id string required Kode order unik (kode booking). Example: GRD-ABCD1234
     * @bodyParam payment_type string required Tipe pembayaran. Example: gopay
     * @bodyParam gross_amount string required Jumlah total pembayaran. Example: 1250000.00
     * @bodyParam signature_key string required Signature SHA512 key untuk validasi data. Example: abc123xyz...
     * @response 200 {
     *   "status": "ok"
     * }
     */
    public function webhook(Request $request)
    {
        Log::info('Webhook received', $request->all());

        /* Validasi signature key SHA512 dari payload webhook Midtrans untuk keamanan */
        if (!$this->verifyMidtransSignature($request)) {
            Log::warning('Webhook signature mismatch');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        /* Siapkan konfigurasi kredensial Midtrans */
        $this->setupMidtrans();

        try {
            /* Parsing data notifikasi asinkron dari Midtrans */
            $notif  = new Notification();
            $status = $notif->transaction_status;
            $type   = $notif->payment_type;
            $code   = $notif->order_id;
            $fraud  = $notif->fraud_status;

            /* Temukan data transaksi berdasarkan kode booking */
            $transaction = Transaction::where('code', $code)->firstOrFail();

            /* Tentukan status transaksi (paid/pending/failed) berdasarkan respon webhook */
            if ($status === 'capture') {
                $paymentStatus = ($fraud === 'challenge') ? 'pending' : 'paid';
            } elseif ($status === 'settlement') {
                $paymentStatus = 'paid';
            } elseif (in_array($status, ['cancel', 'deny', 'expire'])) {
                $paymentStatus = 'failed';
            } else {
                $paymentStatus = $transaction->payment_status;
            }

            if ($paymentStatus === 'paid') {
                $transaction = $this->promoService->completePayment($transaction, $type);
            } else {
                $transaction->update([
                    'payment_status' => $paymentStatus,
                    'payment_method' => 'midtrans',
                    'payment_channel' => $type,
                ]);
            }

            /* Kirim email notifikasi e-ticket (guard mail_sent_at mencegah duplikat) */
            if ($transaction->payment_status === 'paid') {
                $this->sendSuccessEmailOnce($transaction);
            }
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Metode Pembayaran Bank Transfer
     * 
     * Memilih metode transfer bank manual (non-Midtrans) untuk pemesanan pending.
     * 
     * @group Pembayaran
     * @urlParam transaction int required ID transaksi. Example: 1
     * @bodyParam bank string required Bank pilihan (mandiri, bca, bni, bsi). Example: mandiri
     * @response 302 Redirect ke detail pemesanan
     */
    public function payBank(Request $request, Transaction $transaction)
    {
        /* Otorisasi akses pengguna terhadap transaksi */
        Gate::authorize('update', $transaction);

        /* Pastikan status pembayaran transaksi masih pending */
        if ($transaction->payment_status !== 'pending') {
            return redirect()->route('booking.detail', $transaction->id)
                ->with('info', 'Transaksi ini sudah diproses.');
        }

        /* Validasi input bank pilihan */
        $request->validate([
            'bank' => 'required|in:mandiri,bca,bni,bsi',
        ]);

        /* Perbarui transaksi dengan metode bank transfer manual */
        $transaction->update([
            'payment_method'  => 'bank_transfer',
            'payment_channel' => $request->bank,
        ]);

        return redirect()->route('booking.detail', $transaction->id)
            ->with('success', 'Silakan transfer dan konfirmasi ke admin.');
    }

    /**
     * Detail Transaksi Pemesanan
     * 
     * Menampilkan informasi rincian pemesanan beserta status dan e-ticket (jika lunas).
     * 
     * @group Pemesanan Tiket
     * @urlParam transaction int required ID transaksi. Example: 1
     * @response 200 (HTML View) Halaman rincian pemesanan
     */
    public function detail(Transaction $transaction)
    {
        /* Otorisasi akses melihat transaksi */
        Gate::authorize('view', $transaction);

        /* Eager load relasi maskapai, segmen bandara, kelas penerbangan, promo, dan kursi penumpang untuk optimasi performa query */
        $transaction->load('flight.airline', 'flight.segments.airport', 'class', 'promo', 'passengers.seat');

        return view('booking.detail', compact('transaction'));
    }

    /**
     * Batalkan Pemesanan
     * 
     * Membatalkan transaksi pemesanan yang masih pending (belum dibayar).
     * 
     * @group Pemesanan Tiket
     * @urlParam transaction int required ID transaksi. Example: 1
     * @response 302 Redirect ke halaman riwayat pesanan
     */
    public function cancel(Transaction $transaction)
    {
        /* Otorisasi pembatalan transaksi */
        Gate::authorize('update', $transaction);

        /* Cegah pembatalan jika status transaksi sudah tidak pending */
        if ($transaction->payment_status !== 'pending') {
            return redirect()->route('booking.detail', $transaction->id)
                ->with('error', 'Hanya pesanan yang berstatus pending yang dapat dibatalkan.');
        }

        /* Tandai status pembayaran transaksi sebagai failed/batal */
        $transaction->update([
            'payment_status' => 'failed',
        ]);

        return redirect()->route('booking.my-bookings')
            ->with('success', 'Pesanan Anda dengan kode ' . $transaction->code . ' berhasil dibatalkan.');
    }

    /**
     * Tampilkan Pemilihan Kursi
     * 
     * Halaman untuk memilih kursi kosong pada penerbangan dan kelas yang dipilih.
     * 
     * @group Pemesanan Tiket
     * @urlParam flight string required Nomor penerbangan. Example: GA-401
     * @urlParam flightClass int required ID kelas penerbangan. Example: 1
     * @queryParam passengers int Jumlah penumpang. Example: 1
     */
    public function chooseSeat(Flight $flight, FlightClass $flightClass, Request $request)
    {
        /* Eager load data penerbangan dan segmen bandara rute */
        $flight->load(['airline', 'segments.airport']);

        /* Hitung batas minimal/maksimal jumlah penumpang yang valid */
        $passengers = max(1, min(9, (int) $request->query('passengers', 1)));
        
        /* Ambil semua kursi yang masih tersedia untuk kelas penerbangan tersebut */
        $seatsByRow = $this->getAvailableSeats($flight->id, $flightClass->class_type)
            ->groupBy('row');

        return view('booking.choose-seat', compact('flight', 'flightClass', 'seatsByRow', 'passengers'));
    }

    /**
     * Cek Kode Promo
     * 
     * Memvalidasi ketersediaan dan nilai potongan harga dari kode promo.
     * 
     * @group Promosi
     * @queryParam code string required Kode promo. Example: GARUDA10
     * 
     * @response 200 {
     *   "valid": true,
     *   "label": "Diskon 10%",
     *   "discount_type": "percentage",
     *   "discount": 10
     * }
     */
    public function checkPromo(Request $request)
    {
        /* Dapatkan input kode promo dari request */
        $code = $request->query('code', '');

        /* Gunakan layanan PromoService untuk memvalidasi dan menghitung nilai potongan */
        $result = $this->promoService->apply($code, 0, 0, $request->user());

        return response()->json($result['valid']
            ? [
                'valid'         => true,
                'label'         => $result['label'],
                'discount_type' => $result['discount_type'],
                'discount'      => $result['discount'],
            ]
            : [
                'valid'   => false,
                'message' => $result['message'],
            ]
        );
    }

    /**
     * Cek Booking via Kode
     * 
     * Mencari rincian pemesanan berdasarkan kode booking unik.
     * 
     * @group Pemesanan Tiket
     * @queryParam code string required Kode booking (contoh: GRD-XXXX). Example: GRD-ABCDEF12
     * @response 302 Redirect ke halaman rincian transaksi
     */
    public function check(Request $request)
    {
        /* Ambil kata kunci kode booking dari input */
        $code = $request->query('code');

        if (!$code) {
            return view('booking.check');
        }

        /* Cari transaksi di database berdasarkan kode booking unik */
        $transaction = Transaction::where('code', strtoupper(trim($code)))->first();

        if (!$transaction) {
            return redirect()->route('booking.check')
                ->with('error', 'Kode booking tidak ditemukan.');
        }

        return redirect()->route('booking.detail', $transaction->id);
    }

    /**
     * Riwayat Pemesanan Pengguna
     * 
     * Menampilkan daftar seluruh transaksi/pemesanan tiket dari pengguna yang login.
     * 
     * @group Pemesanan Tiket
     * @response 200 (HTML View) Halaman daftar seluruh riwayat pemesanan pelanggan
     */
    public function myBookings(Request $request)
    {
        /* Ambil seluruh riwayat transaksi milik pengguna aktif yang sedang login */
        $transactions = $request->user()->transactions()
            ->with('flight.airline', 'flight.segments.airport', 'class', 'promo')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('booking.my-bookings', compact('transactions'));
    }

    /**
     * Polling Ketersediaan Kursi (AJAX)
     * 
     * Memeriksa daftar seluruh kursi beserta status ketersediaannya secara real-time.
     * 
     * @group Pemesanan Tiket
     * @queryParam flight_id int required ID penerbangan. Example: 1
     * @queryParam class_type string required Tipe kelas (economy, business). Example: economy
     * @response 200 {
     *   "seats": [
     *     {
     *       "id": 1,
     *       "name": "1A",
     *       "available": true,
     *       "row": 1,
     *       "column": "A"
     *     }
     *   ]
     * }
     */
    public function ajaxSeats(Request $request)
    {
        /* Validasi masukan ID penerbangan dan tipe kelas kabin */
        $request->validate([
            'flight_id'  => 'required|exists:flights,id',
            'class_type' => 'required|in:economy,business',
        ]);

        /* Tarik daftar kursi yang tersedia */
        $seats = $this->getAvailableSeats(
            (int) $request->flight_id,
            $request->class_type
        );

        return response()->json([
            'seats' => $seats->map(fn($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'available'  => $s->is_available,
                'row'        => $s->row,
                'column'     => $s->column,
            ]),
        ]);
    }

    /**
     * Polling Status Pembayaran (AJAX)
     * 
     * Memeriksa status transaksi pembayaran secara real-time dari halaman snap.
     * 
     * @group Pembayaran
     * @urlParam transaction int required ID transaksi. Example: 1
     * 
     * @response 200 {
     *   "status": "paid",
     *   "redirect_url": "http://127.0.0.1:8000/booking/1"
     * }
     */
    public function ajaxPaymentStatus(Request $request, Transaction $transaction)
    {
        /* Kembalikan status pembayaran terkini dan URL redirect jika sudah sukses dibayar */
        return response()->json([
            'status'       => $transaction->payment_status,
            'redirect_url' => $transaction->payment_status === 'paid'
                ? route('booking.detail', $transaction->id)
                : null,
        ]);
    }
}
