<?php

namespace App\Filament\Resources\Flights\Pages;

use App\Filament\Resources\Flights\FlightResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFlight extends CreateRecord
{
    protected static string $resource = FlightResource::class;

   protected function afterCreate(): void
{
    $this->record->load('classes');
    foreach ($this->record->classes as $class) {
        $this->record->generateSeats(
            totalSeats: $class->total_seats,
            seatsPerRow: 6,
            classType: $class->class_type
        );
    }
}
}