<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [
            'total' => Transaction::count(),
            'pending' => Transaction::where('payment_status', 'pending')->sum('grandtotal'),
            'paid' => Transaction::where('payment_status', 'paid')->sum('grandtotal'),
        ];

        return [
            Stat::make('Total Transaksi', $stats['total']),
            Stat::make('Tertunda', 'Rp ' . number_format($stats['pending'], 0, ',', '.')),
            Stat::make('Telah Dibayar', 'Rp ' . number_format($stats['paid'], 0, ',', '.')),
        ];
    }
}
