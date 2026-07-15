<?php

namespace App\Filament\Resources\Facilties\Pages;

use App\Filament\Resources\Facilties\FaciltyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFacilties extends ListRecords
{
    protected static string $resource = FaciltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
