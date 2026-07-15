<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsKpiCards extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $todayStart = $now->copy()->startOfDay()->utc();
        $todayEnd = $now->copy()->endOfDay()->utc();
        $thirtyStart = $now->copy()->subDays(29)->startOfDay()->utc();
        $previousStart = $now->copy()->subDays(59)->startOfDay()->utc();
        $previousEnd = $now->copy()->subDays(30)->endOfDay()->utc();

        $paid = Transaction::query()->where('payment_status', 'paid');
        $thirty = (clone $paid)->whereBetween('created_at', [$thirtyStart, $todayEnd])
            ->selectRaw('COUNT(*) AS total_tx, COALESCE(SUM(number_of_passengers), 0) AS total_pax, COALESCE(SUM(grandtotal), 0) AS revenue, COALESCE(SUM(discount), 0) AS discount_total, COALESCE(SUM(subtotal), 0) AS subtotal_total')
            ->first();
        $today = (clone $paid)->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('COUNT(*) AS total_tx, COALESCE(SUM(grandtotal), 0) AS revenue, COALESCE(SUM(discount), 0) AS discount_total')
            ->first();
        $previousRevenue = (clone $paid)->whereBetween('created_at', [$previousStart, $previousEnd])->sum('grandtotal');

        $revenue30 = (int) $thirty->revenue;
        $todayRevenue = (int) $today->revenue;
        $totalTransactions = (int) $thirty->total_tx;
        $trend = $previousRevenue > 0
            ? (($revenue30 >= $previousRevenue ? '↑ Naik ' : '↓ Turun ') . round(abs($revenue30 - $previousRevenue) / $previousRevenue * 100, 1) . '% vs 30 hari sebelumnya')
            : 'Data transaksi aktual 30 hari terakhir';

        return [
            Stat::make('Pendapatan 30 Hari', $this->formatRupiah($revenue30))->description($trend),
            Stat::make('Pendapatan Hari Ini', $this->formatRupiah($todayRevenue))->description($todayRevenue ? 'Dari transaksi paid hari ini' : 'Belum ada pembayaran hari ini'),
            Stat::make('Rata-rata Tiket', $this->formatRupiah($totalTransactions ? (int) ($thirty->subtotal_total / $totalTransactions) : 0))->description('Nilai tiket rata-rata'),
            Stat::make('Transaksi (30 Hari)', $totalTransactions)->description($thirty->total_pax . ' penumpang'),
            Stat::make('Transaksi Hari Ini', (int) $today->total_tx)->description('Diskon: '.$this->formatRupiah((int) $today->discount_total)),
            Stat::make('Total Penumpang', (int) $thirty->total_pax)->description('30 hari terakhir'),
        ];
    }

    private function formatRupiah(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }
}
