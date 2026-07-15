<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Transaction;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('promo:sync-usages', function () {
    $created = 0;

    DB::transaction(function () use (&$created) {
        Transaction::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('promo_code_id')
            ->whereDoesntHave('promoCodeUsage')
            ->lockForUpdate()
            ->get()
            ->each(function (Transaction $transaction) use (&$created) {
                $alreadyUsed = PromoCodeUsage::query()
                    ->where('promo_code_id', $transaction->promo_code_id)
                    ->where('user_id', $transaction->user_id)
                    ->exists();

                if ($alreadyUsed) {
                    $this->warn("Transaksi {$transaction->code} dilewati: user sudah memakai promo ini.");

                    return;
                }

                PromoCodeUsage::create([
                    'promo_code_id' => $transaction->promo_code_id,
                    'user_id' => $transaction->user_id,
                    'transaction_id' => $transaction->id,
                ]);

                $created++;
            });

        PromoCode::query()->lockForUpdate()->get()->each(function (PromoCode $promo) {
            $promo->update(['used_count' => $promo->usages()->count()]);
        });
    });

    $this->info("{$created} pemakaian promo berhasil disinkronkan.");
})->purpose('Sinkronkan usage promo untuk transaksi paid lama');

Artisan::command('olap:refresh', function () {
    DB::transaction(function () {
        DB::table('olap_transaction_daily_summaries')->delete();
        DB::table('olap_route_daily_summaries')->delete();

        DB::statement(<<<'SQL'
            INSERT INTO olap_transaction_daily_summaries (
                summary_date,
                payment_status,
                flight_id,
                airline_id,
                transaction_count,
                passenger_count,
                subtotal_sum,
                discount_sum,
                grandtotal_sum,
                created_at,
                updated_at
            )
            SELECT
                DATE(transactions.created_at) AS summary_date,
                transactions.payment_status,
                transactions.flight_id,
                flights.airline_id,
                COUNT(transactions.id) AS transaction_count,
                COALESCE(SUM(transactions.number_of_passengers), 0) AS passenger_count,
                COALESCE(SUM(transactions.subtotal), 0) AS subtotal_sum,
                COALESCE(SUM(transactions.discount), 0) AS discount_sum,
                COALESCE(SUM(transactions.grandtotal), 0) AS grandtotal_sum,
                NOW(),
                NOW()
            FROM transactions
            INNER JOIN flights ON flights.id = transactions.flight_id
            WHERE transactions.deleted_at IS NULL
            GROUP BY DATE(transactions.created_at), transactions.payment_status, transactions.flight_id, flights.airline_id
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO olap_route_daily_summaries (
                summary_date,
                departure_airport_id,
                arrival_airport_id,
                flight_count,
                paid_transaction_count,
                passenger_count,
                revenue_sum,
                created_at,
                updated_at
            )
            SELECT
                DATE(transactions.created_at) AS summary_date,
                departure.airport_id AS departure_airport_id,
                arrival.airport_id AS arrival_airport_id,
                COUNT(DISTINCT transactions.flight_id) AS flight_count,
                COUNT(transactions.id) AS paid_transaction_count,
                COALESCE(SUM(transactions.number_of_passengers), 0) AS passenger_count,
                COALESCE(SUM(transactions.grandtotal), 0) AS revenue_sum,
                NOW(),
                NOW()
            FROM transactions
            INNER JOIN (
                SELECT fs.flight_id, fs.airport_id
                FROM flight_segments fs
                INNER JOIN (
                    SELECT flight_id, MIN(sequence) AS sequence
                    FROM flight_segments
                    WHERE deleted_at IS NULL
                    GROUP BY flight_id
                ) first_segments
                    ON first_segments.flight_id = fs.flight_id
                    AND first_segments.sequence = fs.sequence
                WHERE fs.deleted_at IS NULL
            ) departure ON departure.flight_id = transactions.flight_id
            INNER JOIN (
                SELECT fs.flight_id, fs.airport_id
                FROM flight_segments fs
                INNER JOIN (
                    SELECT flight_id, MAX(sequence) AS sequence
                    FROM flight_segments
                    WHERE deleted_at IS NULL
                    GROUP BY flight_id
                ) last_segments
                    ON last_segments.flight_id = fs.flight_id
                    AND last_segments.sequence = fs.sequence
                WHERE fs.deleted_at IS NULL
            ) arrival ON arrival.flight_id = transactions.flight_id
            WHERE transactions.deleted_at IS NULL
              AND transactions.payment_status = 'paid'
            GROUP BY DATE(transactions.created_at), departure.airport_id, arrival.airport_id
        SQL);

    });

    $this->info('OLAP summaries refreshed.');
})->purpose('Refresh denormalized OLAP summary tables from OLTP booking data');
