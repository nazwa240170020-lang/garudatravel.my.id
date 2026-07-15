<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;   
use Filament\Actions\DeleteAction; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with([
                    'flight',
                    'class',
                    'promo',
                ])
                ->withCount('passengers'))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M Y, H:i', 'Asia/Jakarta')
                    ->sortable(),

                TextColumn::make('flight.flight_number')
                    ->label('Penerbangan')
                    ->sortable(),

                TextColumn::make('number_of_passengers')
                    ->label('Jumlah Penumpang')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('passengers_count')
                    ->label('Penumpang')
                    ->counts('passengers')
                    ->sortable(),


                TextColumn::make('promo.code')
                    ->label('Promo')
                    ->default('-'),

                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('grandtotal')
                    ->label('Total Keseluruhan')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
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
