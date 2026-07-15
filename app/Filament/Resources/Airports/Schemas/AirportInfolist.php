<?php

namespace App\Filament\Resources\Airports\Schemas;

use App\Models\Airport;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AirportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('iata_code')
                    ->label('Kode'),

                TextEntry::make('name')
                    ->label('Nama Bandara'),

                ImageEntry::make('image')
                    ->label('Gambar Bandara')
                    ->disk('public')
                    ->height(200),

                TextEntry::make('city'),

                TextEntry::make('country'),

                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Airport $record): bool => $record->trashed()),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}