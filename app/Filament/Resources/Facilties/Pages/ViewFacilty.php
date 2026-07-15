<?php

namespace App\Filament\Resources\Facilties\Pages;

use App\Filament\Resources\Facilties\FaciltyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFacilty extends ViewRecord
{
    protected static string $resource = FaciltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
