<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Transaksi')
                            ->required(),

                        Select::make('flight_id')
                            ->label('Penerbangan')
                            ->relationship('flight', 'flight_number')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('flight_class_id')
                            ->label('Kelas Penerbangan')
                            ->relationship('class', 'class_type')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('promo_code_id')
                            ->label('Kode Promo')
                            ->relationship('promo', 'code')
                            ->searchable()
                            ->preload()
                            ->placeholder('Tidak ada promo'),
                    ])->columns(2),

                Section::make('Informasi Pelanggan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required(),

                        TextInput::make('number_of_passengers')
                            ->label('Jumlah Penumpang')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])->columns(2),

                Section::make('Pembayaran')
                    ->schema([
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Tertunda',
                                'paid'    => 'Dibayar',
                                'failed'  => 'Gagal',
                            ])
                            ->default('pending')
                            ->required(),

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('IDR'),

                        TextInput::make('grandtotal')
                            ->label('Total Keseluruhan')
                            ->numeric()
                            ->prefix('IDR'),
                    ])->columns(3),

                Section::make('Penumpang')
                    ->schema([
                        Repeater::make('passengers')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Penumpang')
                                    ->required(),

                                TextInput::make('date_of_birth')  // ← ganti id_number jadi date_of_birth
                                    ->label('Tanggal Lahir')
                                    ->type('date')
                                    ->required(),

                                TextInput::make('nationality')    // ← tambah nationality
                                    ->label('Kewarganegaraan')
                                    ->required(),

                                Select::make('flight_seat_id')    // ← ganti seat_id jadi flight_seat_id
                                    ->label('Kursi')
                                    ->relationship('seat', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsed(false)
                            ->minItems(1),
                    ]),
            ]);
    }
}