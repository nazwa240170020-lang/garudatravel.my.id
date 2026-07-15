<?php

namespace App\Filament\Resources\SiteSections\Pages;

use App\Filament\Resources\SiteSections\SiteSectionResource;
use Filament\Resources\Pages\EditRecord;

class EditSiteSection extends EditRecord
{
    protected static string $resource = SiteSectionResource::class;

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}
