<?php

namespace App\Filament\Resources\Flights\Schemas;

use App\Models\Airline;
use App\Models\Airport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class FlightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informasi Penerbangan')
                        ->schema([
                            TextInput::make('flight_number')
                                ->required()
                                ->unique(ignoreRecord: true),

                            Select::make('airline_id')
                                ->relationship('airline', 'name')
                                ->getOptionLabelFromRecordUsing(
                                    fn (Airline $record) => "{$record->iata_code} - {$record->name}"
                                )
                                ->searchable(['iata_code', 'name'])
                                ->preload()
                                ->required(),

                            Placeholder::make('route_duration')
                                ->label('Rute & Durasi')
                                ->content(function ($record): string {
                                    if (!$record) {
                                        return '-';
                                    }

                                    $record->load('segments.airport');

                                    if ($record->segments->isEmpty()) {
                                        return '-';
                                    }

                                    $firstSegment = $record->segments->first();
                                    $lastSegment  = $record->segments->last();

                                    $route = $firstSegment->airport->iata_code
                                        . ' - '
                                        . $lastSegment->airport->iata_code;

                                    $duration = (new \DateTime($firstSegment->time))
                                        ->format('d F Y H:i')
                                        . ' - '
                                        . (new \DateTime($lastSegment->time))
                                        ->format('d F Y H:i');

                                    return $route . ' | ' . $duration;
                                })
                                ->visibleOn('edit'),
                        ]),

                    Step::make('Segmen Penerbangan')
                        ->schema([
                            Repeater::make('segments')
                                ->relationship()
                                ->schema([
                                    TextInput::make('sequence')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1),

                                    Select::make('airport_id')
                                        ->relationship('airport', 'name')
                                        ->getOptionLabelFromRecordUsing(
                                            fn (Airport $record) => "{$record->iata_code} - {$record->name}"
                                        )
                                        ->searchable(['iata_code', 'name'])
                                        ->preload()
                                        ->required(),

                                    DateTimePicker::make('time')
                                        ->required()
                                        ->seconds(false),
                                ])
                                ->collapsed(false)
                                ->minItems(2) // ← minimal 2 segment (origin + destination)
                                ->defaultItems(2) // ← langsung tampil 2 baris saat create
                                ->reorderable() // ← bisa drag-drop urutan segment
                                ->addActionLabel('Tambah Segmen') // ← label tombol tambah
                                ->rules([
                                    // Validasi: semua airport_id tidak boleh sama semua
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if (empty($value)) return;

                                            $airportIds = collect($value)
                                                ->pluck('airport_id')
                                                ->filter()
                                                ->values();

                                            if ($airportIds->count() >= 2 && $airportIds->unique()->count() === 1) {
                                                $fail('Bandara asal dan tujuan harus berbeda.');
                                            }
                                        };
                                    }
                                ]),
                        ]),

                    Step::make('Kelas Penerbangan')
                        ->schema([
                            Repeater::make('classes')
                                ->relationship()
                                ->schema([
                                    Select::make('class_type')
                                        ->options([
                                            'business' => 'Bisnis',
                                            'economy'  => 'Ekonomi',
                                        ])
                                        ->required(),

                                    TextInput::make('price')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required(),

                                    TextInput::make('total_seats')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1)
                                        ->label('Total Kursi'),

                                    Select::make('facilties')
                                        ->relationship('facilties', 'name')
                                        ->multiple()
                                        ->preload()
                                        ->searchable(),
                                ])
                                ->collapsed(false)
                                ->minItems(1),
                        ]),
                ])->columnSpanFull(),
            ]);
    }
}