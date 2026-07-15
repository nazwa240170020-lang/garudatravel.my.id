<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AnalyticsRouteChart extends ChartWidget
{
    protected static ?int $sort = 5;
    protected function getType(): string { return 'bar'; }
    public function getHeading(): ?string { return '10 Rute dengan Pendapatan Tertinggi'; }

    protected function getData(): array
    {
        $rows = DB::table('olap_route_daily_summaries')
            ->leftJoin('airports AS dep', 'dep.id', '=', 'olap_route_daily_summaries.departure_airport_id')
            ->leftJoin('airports AS arr', 'arr.id', '=', 'olap_route_daily_summaries.arrival_airport_id')
            ->where('summary_date', '>=', now()->subDays(30))
            ->selectRaw("CONCAT(dep.iata_code, ' → ', arr.iata_code) AS route, SUM(revenue_sum) AS total_revenue")
            ->groupBy('departure_airport_id', 'arrival_airport_id', 'dep.iata_code', 'arr.iata_code')->orderByDesc('total_revenue')->limit(10)->get();

        return ['datasets' => [['label' => 'Pendapatan (Rp)', 'data' => $rows->pluck('total_revenue')->map(fn ($value) => (int) $value)->all(), 'backgroundColor' => '#3b82f6']], 'labels' => $rows->isEmpty() ? ['Belum ada data'] : $rows->pluck('route')->all()];
    }
}
