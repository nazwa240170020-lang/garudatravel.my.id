<?php

namespace App\Filament\Widgets;

use App\Models\OlapRouteDailySummary;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AnalyticsRouteTable extends BaseWidget
{
    protected static ?int $sort = 8;

    protected function getTableHeading(): ?string { return 'Performa Rute Teratas'; }

    public function table(Table $table): Table
    {
        return $table->query(OlapRouteDailySummary::query()
            ->leftJoin('airports AS dep', 'dep.id', '=', 'olap_route_daily_summaries.departure_airport_id')
            ->leftJoin('airports AS arr', 'arr.id', '=', 'olap_route_daily_summaries.arrival_airport_id')
            ->where('summary_date', '>=', now()->subDays(30))
            ->selectRaw('MAX(olap_route_daily_summaries.id) AS id, MAX(dep.iata_code) AS departure, MAX(arr.iata_code) AS arrival, SUM(paid_transaction_count) AS total_paid, SUM(revenue_sum) AS total_revenue, COALESCE(SUM(revenue_sum) / NULLIF(SUM(paid_transaction_count), 0), 0) AS avg_ticket')
            ->groupBy('departure_airport_id', 'arrival_airport_id')->orderByDesc('total_revenue')->limit(5))
            ->columns([
                TextColumn::make('departure')->label('Dari'),
                TextColumn::make('arrival')->label('Ke'),
                TextColumn::make('total_paid')->label('Dibayar')->numeric(),
                TextColumn::make('avg_ticket')->label('Rata-rata Tiket')->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('total_revenue')->label('Pendapatan')->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.')),
            ])->paginated(false)->defaultKeySort(false);
    }
}
