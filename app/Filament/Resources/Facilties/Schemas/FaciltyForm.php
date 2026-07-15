<?php

namespace App\Filament\Resources\Facilties\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FaciltyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('facilties')
                    ->visibility('public')
                    ->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('160')
                    ->openable()
                    ->downloadable()
                    ->columnSpan(2)
                    ->required(),

                    TextInput::make('name')
                    ->required()
                    ->maxLength(255),
              
                TextInput::make('description')
                     ->required()
                    ->maxLength(1000),
            ]);
    }
}
