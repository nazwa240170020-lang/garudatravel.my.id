<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\TransactionPassenger;

/*
 * Komponen Livewire SeatMap
 *
 * Mengelola antarmuka peta kursi pesawat secara real-time, memungkinkan pengguna
 * untuk memilih kursi kosong sesuai kapasitas pemesanan, serta melakukan sinkronisasi
 * status ketersediaan kursi secara asinkron (polling/web socket).
 */
class SeatMap extends Component
{
    /* @var int ID Penerbangan aktif */
    public $flightId;

    /* @var int ID Kelas Penerbangan aktif */
    public $flightClassId;

    /* @var int Kapasitas jumlah kursi yang boleh dipesan */
    public $passengers;

    /* @var array List nama kursi terpilih (misal ['1A', '1D']) */
    public $selectedSeats = [];

    /*
     * Listener Event WebSocket/Laravel Echo
     * Memperbarui data kursi secara real-time ketika ada status kursi yang diupdate dari transaksi lain.
     */
    protected $listeners = ['echo:flights,SeatStatusUpdated' => '$refresh'];

    /*
     * Inisialisasi properti komponen saat mount
     *
     * @param int $flightId
     * @param int $flightClassId
     * @param int $passengers
     * @return void
     */
    public function mount($flightId, $flightClassId, $passengers)
    {
        $this->flightId = $flightId;
        $this->flightClassId = $flightClassId;
        $this->passengers = $passengers;
    }

    /*
     * Aksi memilih atau membatalkan pilihan kursi
     *
     * @param string $seatName Kode nama kursi
     * @param bool $isAvailable Status ketersediaan kursi
     * @return void
     */
    public function selectSeat($seatName, $isAvailable)
    {
        if (!$isAvailable) return;

        if (in_array($seatName, $this->selectedSeats)) {
            $this->selectedSeats = array_values(array_diff($this->selectedSeats, [$seatName]));
        } else {
            if (count($this->selectedSeats) >= $this->passengers) {
                $this->dispatch('alert', message: 'Kamu hanya bisa memilih ' . $this->passengers . ' kursi sesuai jumlah penumpang.');
                return;
            }
            $this->selectedSeats[] = $seatName;
        }

        $this->dispatch('seats-updated', seats: implode(',', $this->selectedSeats));
    }

    /*
     * Dapatkan daftar kursi yang dikelompokkan berdasarkan baris
     *
     * Memeriksa daftar kursi penerbangan dan mencocokkan status ketersediaan
     * dengan data transaksi penumpang yang sudah lunas atau pending (belum failed).
     *
     * @return \Illuminate\Support\Collection Kursi terkelompok baris
     */
    public function getSeatsByRow()
    {
        $flight = Flight::find($this->flightId);
        $flightClass = FlightClass::find($this->flightClassId);

        $bookedIds = TransactionPassenger::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_passengers.transaction_id')
            ->where('transactions.flight_id', $this->flightId)
            ->where('transactions.payment_status', '!=', 'failed')
            ->whereNull('transactions.deleted_at')
            ->whereNull('transaction_passengers.deleted_at')
            ->pluck('transaction_passengers.flight_seat_id');

        return FlightSeat::where('flight_id', $this->flightId)
            ->where('class_type', $flightClass->class_type)
            ->orderBy('row')
            ->orderBy('column')
            ->get()
            ->each(function ($seat) use ($bookedIds) {
                $seat->is_available = !$bookedIds->contains($seat->id);
            })
            ->groupBy('row');
    }

    /*
     * Render view komponen Livewire
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.seat-map-view', [
            'seatsByRow' => $this->getSeatsByRow(),
            'classType' => FlightClass::find($this->flightClassId)->class_type
        ]);
    }
}
