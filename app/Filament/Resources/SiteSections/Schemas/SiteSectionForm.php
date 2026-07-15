<?php

namespace App\Filament\Resources\SiteSections\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SiteSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live()
                    ->disabled(fn ($record) => $record !== null)
                    ->options([
                        'hotels' => 'Hotel',
                        'schedule' => 'Jadwal',
                        'testimonial' => 'Testimoni',
                        'call-us' => 'Hubungi Kami',
                    ]),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('subtitle')
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->default(true),

                // — Hotels
                Repeater::make('data.items')
                    ->label('Daftar Hotel')
                    ->visible(fn (Get $get) => $get('slug') === 'hotels')
                    ->schema([
                        TextInput::make('name')->label('Nama Hotel')->required(),
                        TextInput::make('city')->label('Kota')->required(),
                        TextInput::make('price')->label('Harga per Malam')->numeric()->required(),
                        TextInput::make('rating')->label('Penilaian (1-5)')->numeric()->minValue(1)->maxValue(5)->required(),
                        TextInput::make('image')->label('URL Gambar')->url()->required(),
                    ])
                    ->columns(2),

                // — Schedule
                Repeater::make('data.items')
                    ->label('Daftar Jadwal')
                    ->visible(fn (Get $get) => $get('slug') === 'schedule')
                    ->schema([
                        TextInput::make('airline')->label('Maskapai')->required(),
                        TextInput::make('from')->label('Kode Bandara Asal')->required(),
                        TextInput::make('from_city')->label('Kota Asal'),
                        TextInput::make('to')->label('Kode Bandara Tujuan')->required(),
                        TextInput::make('to_city')->label('Kota Tujuan'),
                        TextInput::make('depart')->label('Jam Berangkat'),
                        TextInput::make('arrive')->label('Jam Tiba'),
                        TextInput::make('duration')->label('Durasi'),
                        TextInput::make('transit')->label('Transit')->numeric()->default(0),
                        TextInput::make('price')->label('Harga')->numeric()->required(),
                    ])
                    ->columns(3),

                // — Testimonial
                Repeater::make('data.items')
                    ->label('Daftar Testimonial')
                    ->visible(fn (Get $get) => $get('slug') === 'testimonial')
                    ->schema([
                        TextInput::make('name')->label('Nama')->required(),
                        TextInput::make('role')->label('Peran'),
                        Textarea::make('message')->label('Pesan')->rows(3)->required(),
                        TextInput::make('avatar')->label('URL Avatar')->url(),
                        TextInput::make('rating')->label('Penilaian (1-5)')->numeric()->minValue(1)->maxValue(5)->required(),
                    ])
                    ->columns(2),

                // — Call Us
                TextInput::make('data.phone')
                    ->label('Nomor Telepon')
                    ->visible(fn (Get $get) => $get('slug') === 'call-us'),
                TextInput::make('data.email')
                    ->label('Alamat Email')
                    ->email()
                    ->visible(fn (Get $get) => $get('slug') === 'call-us'),
                TextInput::make('data.hours')
                    ->label('Jam Operasional')
                    ->visible(fn (Get $get) => $get('slug') === 'call-us'),
            ]);
    }
}
