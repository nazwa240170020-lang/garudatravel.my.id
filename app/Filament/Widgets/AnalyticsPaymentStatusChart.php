<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AnalyticsPaymentStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): ?string
    {
        return 'Distribusi Status Pembayaran';
    }

    protected function getData(): array
    {
        $rows = Transaction::query()
            ->where('created_at', '>=', Carbon::now('Asia/Jakarta')->subDays(29)->startOfDay()->utc())
            ->selectRaw('payment_status, COALESCE(SUM(grandtotal), 0) AS total')
            ->groupBy('payment_status')
            ->get();

        if ($rows->isEmpty()) {
            return ['datasets' => [['data' => [1], 'backgroundColor' => ['#6b7280']]], 'labels' => ['Belum ada data']];
        }

            $labels = $rows->pluck('payment_status')->map(fn ($s) => ucfirst($s))->toArray();
            $data = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

            $colors = [
                'paid' => '#22c55e',
                'pending' => '#eab308',
                'failed' => '#ef4444',
                'expired' => '#6b7280',
                'refunded' => '#f97316',
            ];

            $bgColors = array_map(fn ($s) => $colors[$s] ?? '#6b7280', $rows->pluck('payment_status')->toArray());

        return [
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $bgColors,
                    ],
                ],
                'labels' => $labels,
        ];
    }
}
