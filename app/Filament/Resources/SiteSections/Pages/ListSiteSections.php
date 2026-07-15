<?php

namespace App\Filament\Resources\SiteSections\Pages;

use App\Filament\Resources\SiteSections\SiteSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteSections extends ListRecords
{
    protected static string $resource = SiteSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
