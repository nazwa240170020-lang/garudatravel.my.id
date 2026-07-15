<?php

namespace App\Filament\Resources\Airlines\Schemas;

use App\Models\Airline;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AirlineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('iata_code')->label('Kode'),
                TextEntry::make('name')->label('Nama'),
                ImageEntry::make('logo_url')->label('Logo'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Airline $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
