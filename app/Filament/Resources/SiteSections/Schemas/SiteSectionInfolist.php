<?php

namespace App\Filament\Resources\SiteSections\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SiteSectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('slug')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hotels' => 'Hotel',
                        'schedule' => 'Jadwal',
                        'testimonial' => 'Testimoni',
                        'call-us' => 'Hubungi Kami',
                        default => $state,
                    }),
                TextEntry::make('title'),
                TextEntry::make('subtitle'),
                IconEntry::make('is_active')
                    ->boolean(),

                // Hotels
                RepeatableEntry::make('data.items')
                    ->label('Daftar Hotel')
                    ->visible(fn ($record) => $record?->slug === 'hotels')
                    ->schema([
                        TextEntry::make('name')->label('Nama Hotel'),
                        TextEntry::make('city')->label('Kota'),
                        TextEntry::make('price')
                            ->label('Harga per Malam')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                        TextEntry::make('rating')->label('Penilaian (1-5)'),
                    ]),

                // Schedule
                RepeatableEntry::make('data.items')
                    ->label('Daftar Jadwal')
                    ->visible(fn ($record) => $record?->slug === 'schedule')
                    ->schema([
                        TextEntry::make('airline')->label('Maskapai'),
                        TextEntry::make('from')->label('Kode Bandara Asal'),
                        TextEntry::make('to')->label('Kode Bandara Tujuan'),
                        TextEntry::make('depart')->label('Jam Berangkat'),
                        TextEntry::make('arrive')->label('Jam Tiba'),
                        TextEntry::make('duration')->label('Durasi'),
                        TextEntry::make('transit')->label('Transit'),
                        TextEntry::make('price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                    ]),

                // Testimonial
                RepeatableEntry::make('data.items')
                    ->label('Daftar Testimonial')
                    ->visible(fn ($record) => $record?->slug === 'testimonial')
                    ->schema([
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('role')->label('Peran'),
                        TextEntry::make('message')->label('Pesan'),
                        TextEntry::make('rating')->label('Penilaian (1-5)'),
                    ]),

                // Call Us
                TextEntry::make('data.phone')
                    ->label('Telepon')
                    ->visible(fn ($record) => $record?->slug === 'call-us'),
                TextEntry::make('data.email')
                    ->label('Alamat Email')
                    ->visible(fn ($record) => $record?->slug === 'call-us'),
                TextEntry::make('data.hours')
                    ->label('Jam Operasional')
                    ->visible(fn ($record) => $record?->slug === 'call-us'),

                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
