<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AnalyticsForecastWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Proyeksi Pendapatan 7 Hari';
    }

    public function getDescription(): ?string
    {
        $stats = $this->getRegressionStats();
        return $stats['description'];
    }

    protected function getData(): array
    {
        $rows = $this->olapRevenueRows();

            if ($rows->isEmpty()) {
                return [
                    'datasets' => [
                        ['label' => 'Historis', 'data' => [0], 'borderColor' => '#3b82f6'],
                        ['label' => 'Perkiraan', 'data' => [], 'borderColor' => '#ef4444', 'borderDash' => [5, 5]],
                    ],
                    'labels' => ['Belum ada data'],
                ];
            }

            $dates = $rows->pluck('summary_date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();
            $revenues = $rows->pluck('total_revenue')->map(fn ($v) => (int) $v)->toArray();

            $n = count($revenues);
            $x = range(1, $n);
            $xMean = array_sum($x) / $n;
            $yMean = array_sum($revenues) / $n;

            $num = 0;
            $den = 0;
            for ($i = 0; $i < $n; $i++) {
                $num += ($x[$i] - $xMean) * ($revenues[$i] - $yMean);
                $den += ($x[$i] - $xMean) ** 2;
            }

            $slope = $den != 0 ? $num / $den : 0;
            $intercept = $yMean - $slope * $xMean;

            $trend = $slope > 0 ? 'Naik' : ($slope < 0 ? 'Turun' : 'Stabil');
            $change = round(abs($slope) * 7);
            $description = "Proyeksi {$trend} ~Rp" . number_format($change, 0, ',', '.') . ' dalam 7 hari';

            $historicalData = [];
            $forecastData = array_fill(0, $n, null);

            for ($i = 0; $i < $n; $i++) {
                $historicalData[] = $revenues[$i];
            }

            $forecastLabels = $dates;
            for ($i = 1; $i <= 7; $i++) {
                $predicted = max(0, (int) ($intercept + $slope * ($n + $i)));
                $forecastData[] = $predicted;
                $forecastLabels[] = 'H+' . $i;
            }

        return [
                'datasets' => [
                    [
                        'label' => 'Historis',
                        'data' => $historicalData,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.05)',
                        'fill' => false,
                        'tension' => 0.3,
                    ],
                    [
                        'label' => 'Perkiraan',
                        'data' => $forecastData,
                        'borderColor' => '#ef4444',
                        'borderDash' => [6, 3],
                        'pointRadius' => 4,
                        'pointBackgroundColor' => '#ef4444',
                        'tension' => 0,
                    ],
                ],
                'labels' => $forecastLabels,
        ];
    }

    private function getRegressionStats(): array
    {
        $rows = $this->olapRevenueRows();

            if ($rows->isEmpty()) {
                return ['description' => 'Belum cukup data untuk prediksi'];
            }

            $n = $rows->count();
            $revenues = $rows->pluck('total_revenue')->map(fn ($v) => (int) $v)->toArray();
            $x = range(1, $n);
            $xMean = array_sum($x) / $n;
            $yMean = array_sum($revenues) / $n;

            $num = 0;
            $den = 0;
            for ($i = 0; $i < $n; $i++) {
                $num += ($x[$i] - $xMean) * ($revenues[$i] - $yMean);
                $den += ($x[$i] - $xMean) ** 2;
            }

            $slope = $den != 0 ? $num / $den : 0;
            $trend = $slope > 0 ? 'Naik' : ($slope < 0 ? 'Turun' : 'Stabil');
            $change = round(abs($slope) * 7);

        return [
                'description' => "Proyeksi {$trend} ~Rp" . number_format($change, 0, ',', '.') . ' dalam 7 hari ke depan',
        ];
    }

    private function olapRevenueRows(): \Illuminate\Support\Collection
    {
        return DB::table('olap_transaction_daily_summaries')
            ->where('summary_date', '>=', now()->subDays(30))
            ->selectRaw('summary_date, SUM(grandtotal_sum) AS total_revenue')
            ->groupBy('summary_date')
            ->orderBy('summary_date')
            ->get();
    }
}
