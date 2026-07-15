<?php

namespace App\Filament\Resources\Facilties\Schemas;

use App\Models\Facilty;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FaciltyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('image')->disk('public'),
                TextEntry::make('name'),
                TextEntry::make('description'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Facilty $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}