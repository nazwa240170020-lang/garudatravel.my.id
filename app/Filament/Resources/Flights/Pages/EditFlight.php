<?php

namespace App\Filament\Resources\Flights\Pages;

use App\Filament\Resources\Flights\FlightResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFlight extends EditRecord
{
    protected static string $resource = FlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $airline = $this->record->airline;
        $iataCode = $airline->iata_code ?? '-';
        $flightNumber = $this->record->flight_number ?? '-';
        $airlineName = $airline->name ?? '-';

        return "Edit Penerbangan — {$iataCode}-{$flightNumber} ({$airlineName})";
    }

    protected function beforeSave(): void
    {
       
        $this->record->seats()->withTrashed()->forceDelete();
    }

    protected function afterSave(): void
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