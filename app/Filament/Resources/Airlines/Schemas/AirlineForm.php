<?php

namespace App\Filament\Resources\Airlines\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AirlineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->disk('public')
                    ->directory('airlines')
                    ->visibility('public')
                    ->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('160')
                    ->openable()
                    ->downloadable()
                    ->columnSpan(2)
                    ->required(),

                TextInput::make('iata_code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(10),

                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
