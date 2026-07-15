<?php

namespace App\Filament\Resources\Facilties;

use App\Filament\Resources\Facilties\Pages\CreateFacilty;
use App\Filament\Resources\Facilties\Pages\EditFacilty;
use App\Filament\Resources\Facilties\Pages\ListFacilties;
use App\Filament\Resources\Facilties\Pages\ViewFacilty;
use App\Filament\Resources\Facilties\Schemas\FaciltyForm;
use App\Filament\Resources\Facilties\Schemas\FaciltyInfolist;
use App\Filament\Resources\Facilties\Tables\FaciltiesTable;
use App\Models\Facilty;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FaciltyResource extends Resource
{
    protected static ?string $model = Facilty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $pluralModelLabel = 'Fasilitas';

    protected static UnitEnum|string|null $navigationGroup = 'Data Master';

    public static function form(Schema $schema): Schema
    {
        return FaciltyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FaciltyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaciltiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilties::route('/'),
            'create' => CreateFacilty::route('/create'),
            'view' => ViewFacilty::route('/{record}'),
            'edit' => EditFacilty::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
