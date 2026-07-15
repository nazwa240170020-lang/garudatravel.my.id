<?php

namespace App\Filament\Resources\Flights\Schemas;

use App\Models\Flight;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FlightInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Penerbangan')
                    ->schema([
                        TextEntry::make('flight_number')
                            ->label('Nomor Penerbangan'),

                        TextEntry::make('airline.name')
                            ->label('Maskapai'),

                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime()
                            ->visible(fn (Flight $record): bool => $record->trashed()),
                    ])
                    ->columnSpan(1),

                Section::make('Segmen Penerbangan')
                    ->schema([
                        RepeatableEntry::make('segments')
                            ->schema([
                                TextEntry::make('sequence')
                                    ->label('Urutan'),

                                TextEntry::make('airport.name')
                                    ->label('Bandara'),

                                TextEntry::make('time')
                                    ->label('Waktu')
                                    ->dateTime(),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpan(1),

                Section::make('Kelas Penerbangan')
                    ->schema([
                        RepeatableEntry::make('classes')
                            ->schema([
                                TextEntry::make('class_type')
                                    ->label('Tipe Kelas')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'business' => 'warning',
                                        'economy'  => 'success',
                                        default    => 'gray',
                                    }),

                                TextEntry::make('price')
                                    ->label('Harga')
                                    ->prefix('IDR ')
                                    ->numeric(),

                                TextEntry::make('total_seats')
                                    ->label('Total Kursi'),

                                TextEntry::make('facilties.name')
                                    ->label('Fasilitas')
                                    ->badge()
                                    ->separator(','),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}