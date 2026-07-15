<?php

namespace App\Filament\Resources\SiteSections;

use App\Filament\Resources\SiteSections\Pages\CreateSiteSection;
use App\Filament\Resources\SiteSections\Pages\EditSiteSection;
use App\Filament\Resources\SiteSections\Pages\ListSiteSections;
use App\Filament\Resources\SiteSections\Pages\ViewSiteSection;
use App\Filament\Resources\SiteSections\Schemas\SiteSectionForm;
use App\Filament\Resources\SiteSections\Schemas\SiteSectionInfolist;
use App\Filament\Resources\SiteSections\Tables\SiteSectionsTable;
use App\Models\SiteSection;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SiteSectionResource extends Resource
{
    protected static ?string $model = SiteSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static UnitEnum|string|null $navigationGroup = 'Konten';

    protected static ?string $pluralModelLabel = 'Seksi Konten';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SiteSectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteSectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteSectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSections::route('/'),
            'create' => CreateSiteSection::route('/create'),
            'view' => ViewSiteSection::route('/{record}'),
            'edit' => EditSiteSection::route('/{record}/edit'),
        ];
    }
}
