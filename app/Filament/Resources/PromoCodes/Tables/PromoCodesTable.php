<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
    TextColumn::make('code')
        ->searchable(),

    TextColumn::make('discount_type'),

   TextColumn::make('discount')
    ->label('Diskon')
    ->formatStateUsing(function ($state, $record) {
        return match ($record->discount_type) {
            'percentage' => $state . '%',
            'fixed' => 'Rp ' . number_format($state, 0, ',', '.'),
            default => $state,
        };
    }),

    TextColumn::make('usage')
        ->label('Digunakan')
        ->state(fn ($record) => $record->usage_limit === null ? 'Unlimited' : "{$record->used_count}/{$record->usage_limit}")
        ->badge()
        ->color(fn ($record) => $record->usage_limit === null || $record->used_count === 0
            ? 'gray'
            : ($record->used_count >= $record->usage_limit ? 'danger' : 'success')),

    IconColumn::make('is_active')
        ->label('Aktif')
        ->boolean(),

    TextColumn::make('valid_until')
        ->dateTime(),

    TextColumn::make('created_at')
        ->dateTime()
        ->sortable(),
])
            ->recordActions([
                 ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
