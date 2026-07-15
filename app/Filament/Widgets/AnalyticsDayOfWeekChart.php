<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AnalyticsDayOfWeekChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return 'Pola Pendapatan per Hari';
    }

    protected function getData(): array
    {
        $rows = Transaction::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now('Asia/Jakarta')->subDays(29)->startOfDay()->utc())
            ->selectRaw("DAYOFWEEK(CONVERT_TZ(created_at, '+00:00', '+07:00')) AS dow, COALESCE(AVG(grandtotal), 0) AS avg_revenue")
            ->groupBy('dow')->orderBy('dow')->get();

            $dayNames = ['', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $colors = ['#3b82f6', '#22c55e', '#eab308', '#f97316', '#ef4444', '#a855f7', '#06b6d4'];

            $labels = [];
            $data = [];
            $bgColors = [];

            foreach ($rows as $row) {
                $labels[] = $dayNames[(int) $row->dow] ?? 'Hari ' . $row->dow;
                $data[] = (int) $row->avg_revenue;
                $bgColors[] = $colors[(int) $row->dow - 1] ?? '#6b7280';
            }

        if (empty($data)) {
            return ['datasets' => [['label' => 'Rata-rata Pendapatan', 'data' => [0], 'backgroundColor' => ['#6b7280']]], 'labels' => ['Belum ada data']];
        }

        return [
                'datasets' => [
                    [
                        'label' => 'Rata-rata Pendapatan (Rp)',
                        'data' => $data,
                        'backgroundColor' => $bgColors,
                    ],
                ],
                'labels' => $labels,
        ];
    }
}
