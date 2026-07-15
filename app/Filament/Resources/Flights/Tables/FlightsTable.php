<?php

namespace App\Filament\Resources\Flights\Tables;

use App\Models\Flight;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FlightsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with([
                    'airline',
                    'segments.airport',
                ])
                ->withCount([
                    'classes',
                    'seats',
                    'transactions',
                ]))
            ->columns([
                TextColumn::make('flight_number')
                    ->searchable(),

                TextColumn::make('airline.name')
                    ->label('Maskapai')
                    ->searchable(),

                // Route & Duration (sudah termasuk transit)
                TextColumn::make('route_duration')
                    ->label('Rute & Durasi')
                    ->state(function (Flight $record): string {
                        $segments = $record->segments->sortBy('sequence')->values();

                        if ($segments->isEmpty()) {
                            return '-';
                        }

                        $firstSegment = $segments->first();
                        $lastSegment  = $segments->last();

                        // DPS - CGK - JED
                        $route = $segments
                            ->pluck('airport.iata_code')
                            ->implode(' - ');

                        $duration = (new \DateTime($firstSegment->time))->format('d F Y H:i')
                            . ' - '
                            . (new \DateTime($lastSegment->time))->format('d F Y H:i');

                        $transitCount = max($segments->count() - 2, 0);
                        $transitLabel = $transitCount === 0
                            ? 'Langsung'
                            : "Transit {$transitCount}x";

                        return $route . ' | ' . $duration . ' (' . $transitLabel . ')';
                    }),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
