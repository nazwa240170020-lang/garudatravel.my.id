<?php

use Livewire\Component;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\TransactionPassenger;

new class extends Component
{
    public $flightId;
    public $flightClassId;
    public $passengers;
    public $selectedSeats = [];

    protected $listeners = ['echo:flights,SeatStatusUpdated' => '$refresh'];

    public function mount($flightId, $flightClassId, $passengers)
    {
        $this->flightId = $flightId;
        $this->flightClassId = $flightClassId;
        $this->passengers = $passengers;
    }

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

    public function render()
    {
        return view('livewire.seat-map-view', [
            'seatsByRow' => $this->getSeatsByRow(),
            'classType' => FlightClass::find($this->flightClassId)->class_type
        ]);
    }
};
?>
