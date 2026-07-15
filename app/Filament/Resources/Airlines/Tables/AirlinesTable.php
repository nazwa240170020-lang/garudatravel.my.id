<?php

namespace App\Filament\Resources\Airlines\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AirlinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->square()
                    ->state(function ($record) {
                        if (filter_var($record->logo_url, FILTER_VALIDATE_URL)) {
                            return $record->logo_url;
                        }
                        if ($record->logo_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($record->logo_url)) {
                            return asset('storage/' . $record->logo_url);
                        }
                        return asset('images/logo.svg');
                    }),

                TextColumn::make('iata_code')
                    ->label('Kode')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
