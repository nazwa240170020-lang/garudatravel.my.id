<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AnalyticsRevenueTrend extends ChartWidget
{
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Tren Pendapatan 30 Hari';
    }

    protected function getData(): array
    {
        $start = Carbon::now('Asia/Jakarta')->subDays(29)->startOfDay()->utc();
        $rows = Transaction::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE(CONVERT_TZ(created_at, '+00:00', '+07:00')) AS summary_date, SUM(grandtotal) AS total_revenue")
            ->groupBy('summary_date')
            ->orderBy('summary_date')
            ->get();

        if ($rows->isEmpty()) {
            return ['datasets' => [['label' => 'Pendapatan Harian', 'data' => [0], 'borderColor' => '#3b82f6']], 'labels' => ['Belum ada data']];
        }

            $dates = $rows->pluck('summary_date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();
            $revenues = $rows->pluck('total_revenue')->map(fn ($v) => (int) $v)->toArray();
            $ma7 = $this->movingAverage($revenues, 7);

        return [
                'datasets' => [
                    [
                        'label' => 'Pendapatan Harian',
                        'data' => $revenues,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                    [
                        'label' => 'Rata-rata Bergerak 7 Hari',
                        'data' => $ma7,
                        'borderColor' => '#ef4444',
                        'borderDash' => [5, 5],
                        'pointRadius' => 3,
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $dates,
        ];
    }

    private function movingAverage(array $data, int $window): array
    {
        $result = [];
        $len = count($data);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $window - 1) {
                $result[] = null;
                continue;
            }
            $sum = 0;
            for ($j = $i - $window + 1; $j <= $i; $j++) {
                $sum += $data[$j];
            }
            $result[] = round($sum / $window);
        }
        return $result;
    }
}
