<?php

namespace App\Filament\Resources\SiteSections\Pages;

use App\Filament\Resources\SiteSections\SiteSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteSection extends CreateRecord
{
    protected static string $resource = SiteSectionResource::class;

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}
