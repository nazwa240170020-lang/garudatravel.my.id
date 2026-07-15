<?php

namespace App\Filament\Resources\Airports\Pages;

use App\Filament\Resources\Airports\AirportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\DeleteAction;

class ViewAirport extends ViewRecord
{
    protected static string $resource = AirportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        EditAction::make(),
        DeleteAction::make()
            ->requiresConfirmation()
            ->color('danger'),
    ];
    }
}
