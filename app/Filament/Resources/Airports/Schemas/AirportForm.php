<?php

namespace App\Filament\Resources\Airports\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AirportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                FileUpload::make('image')
                    ->label('Gambar Bandara')
                    ->image()
                    ->disk('public')
                    ->directory('airports')
                    ->visibility('public')
                    ->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->imagePreviewHeight('180')
                    ->openable()
                    ->downloadable()
                    ->columnSpan(2)
                    ->required(),


                TextInput::make('iata_code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(3),

                TextInput::make('name')
                    ->required(),

                TextInput::make('city')
                    ->required(),

                TextInput::make('country')
                    ->required(),
            ]);
    }
}
