<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AnalyticsAirlineChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected function getType(): string { return 'pie'; }
    public function getHeading(): ?string { return '5 Maskapai dengan Pendapatan Tertinggi'; }

    protected function getData(): array
    {
        $rows = DB::table('olap_transaction_daily_summaries')
            ->join('airlines', 'airlines.id', '=', 'olap_transaction_daily_summaries.airline_id')
            ->where('summary_date', '>=', now()->subDays(30))
            ->selectRaw('airlines.name, COALESCE(SUM(grandtotal_sum), 0) AS total')
            ->groupBy('airlines.id', 'airlines.name')->orderByDesc('total')->limit(5)->get();

        return [
            'datasets' => [['data' => $rows->isEmpty() ? [1] : $rows->pluck('total')->map(fn ($value) => (int) $value)->all(), 'backgroundColor' => ['#3b82f6', '#22c55e', '#eab308', '#f97316', '#ef4444']]],
            'labels' => $rows->isEmpty() ? ['Belum ada data'] : $rows->pluck('name')->all(),
        ];
    }
}
