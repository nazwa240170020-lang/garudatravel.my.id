<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        'airports' => [
            ['columns' => ['iata_code'], 'name' => 'airports_iata_code_lookup_idx'],
            ['columns' => ['city', 'country'], 'name' => 'airports_city_country_lookup_idx'],
            ['columns' => ['deleted_at'], 'name' => 'airports_deleted_at_idx'],
        ],
        'airlines' => [
            ['columns' => ['iata_code'], 'name' => 'airlines_iata_code_lookup_idx'],
            ['columns' => ['name'], 'name' => 'airlines_name_lookup_idx'],
            ['columns' => ['deleted_at'], 'name' => 'airlines_deleted_at_idx'],
        ],
        'facilties' => [
            ['columns' => ['name'], 'name' => 'facilties_name_lookup_idx'],
            ['columns' => ['deleted_at'], 'name' => 'facilties_deleted_at_idx'],
        ],
        'flights' => [
            ['columns' => ['airline_id', 'flight_number'], 'name' => 'flights_airline_number_idx'],
            ['columns' => ['deleted_at'], 'name' => 'flights_deleted_at_idx'],
        ],
        'flight_segments' => [
            ['columns' => ['flight_id', 'sequence'], 'name' => 'flight_segments_flight_sequence_idx'],
            ['columns' => ['airport_id', 'sequence', 'time'], 'name' => 'flight_segments_airport_sequence_time_idx'],
            ['columns' => ['flight_id', 'airport_id'], 'name' => 'flight_segments_flight_airport_idx'],
            ['columns' => ['time'], 'name' => 'flight_segments_time_idx'],
            ['columns' => ['deleted_at'], 'name' => 'flight_segments_deleted_at_idx'],
        ],
        'flight_classes' => [
            ['columns' => ['flight_id', 'class_type'], 'name' => 'flight_classes_flight_class_type_idx'],
            ['columns' => ['class_type', 'price'], 'name' => 'flight_classes_type_price_idx'],
            ['columns' => ['deleted_at'], 'name' => 'flight_classes_deleted_at_idx'],
        ],
        'flight_seats' => [
            ['columns' => ['flight_id', 'class_type', 'row', 'column'], 'name' => 'flight_seats_flight_class_position_idx'],
            ['columns' => ['flight_id', 'name'], 'name' => 'flight_seats_flight_name_idx'],
            ['columns' => ['is_available'], 'name' => 'flight_seats_available_idx'],
            ['columns' => ['deleted_at'], 'name' => 'flight_seats_deleted_at_idx'],
        ],
        'flight_class_facilty' => [
            ['columns' => ['facilty_id', 'flight_class_id'], 'name' => 'flight_class_facilty_facilty_class_idx'],
            ['columns' => ['deleted_at'], 'name' => 'flight_class_facilty_deleted_at_idx'],
        ],
        'promo_codes' => [
            ['columns' => ['is_active', 'valid_until'], 'name' => 'promo_codes_active_valid_until_idx'],
            ['columns' => ['deleted_at'], 'name' => 'promo_codes_deleted_at_idx'],
        ],
        'transactions' => [
            ['columns' => ['email', 'payment_status'], 'name' => 'transactions_email_status_idx'],
            ['columns' => ['flight_id', 'payment_status'], 'name' => 'transactions_flight_status_idx'],
            ['columns' => ['payment_status', 'paid_at'], 'name' => 'transactions_status_paid_at_idx'],
            ['columns' => ['payment_method', 'payment_channel'], 'name' => 'transactions_payment_channel_idx'],
            ['columns' => ['created_at', 'payment_status'], 'name' => 'transactions_created_status_idx'],
            ['columns' => ['deleted_at'], 'name' => 'transactions_deleted_at_idx'],
        ],
        'transaction_passengers' => [
            ['columns' => ['flight_seat_id', 'transaction_id'], 'name' => 'transaction_passengers_seat_transaction_idx'],
            ['columns' => ['deleted_at'], 'name' => 'transaction_passengers_deleted_at_idx'],
        ],
        'users' => [
            ['columns' => ['email'], 'name' => 'users_email_lookup_idx'],
            ['columns' => ['deleted_at'], 'name' => 'users_deleted_at_idx'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexes) {
                foreach ($indexes as $index) {
                    if ($this->columnsExist($tableName, $index['columns']) && ! $this->indexExists($tableName, $index['name'])) {
                        $table->index($index['columns'], $index['name']);
                    }
                }
            });
        }

        $this->addUniqueIndexIfClean('airports', ['iata_code'], 'airports_iata_code_unique_norm');
        $this->addUniqueIndexIfClean('airlines', ['iata_code'], 'airlines_iata_code_unique_norm');
        $this->addUniqueIndexIfClean('flight_segments', ['flight_id', 'sequence'], 'flight_segments_flight_sequence_unique_norm');
        $this->addUniqueIndexIfClean('flight_classes', ['flight_id', 'class_type'], 'flight_classes_flight_type_unique_norm');
        $this->addUniqueIndexIfClean('flight_seats', ['flight_id', 'name'], 'flight_seats_flight_name_unique_norm');
        $this->addUniqueIndexIfClean('flight_class_facilty', ['flight_class_id', 'facilty_id'], 'flight_class_facilty_pair_unique_norm');

        if (! Schema::hasTable('olap_transaction_daily_summaries')) {
            Schema::create('olap_transaction_daily_summaries', function (Blueprint $table) {
                $table->id();
                $table->date('summary_date');
                $table->string('payment_status', 20);
                $table->unsignedBigInteger('flight_id')->nullable();
                $table->unsignedBigInteger('airline_id')->nullable();
                $table->unsignedInteger('transaction_count')->default(0);
                $table->unsignedInteger('passenger_count')->default(0);
                $table->bigInteger('subtotal_sum')->default(0);
                $table->bigInteger('discount_sum')->default(0);
                $table->bigInteger('grandtotal_sum')->default(0);
                $table->timestamps();

                $table->unique(['summary_date', 'payment_status', 'flight_id'], 'olap_transaction_daily_unique');
                $table->index(['summary_date', 'payment_status'], 'olap_transaction_daily_date_status_idx');
                $table->index(['airline_id', 'summary_date'], 'olap_transaction_daily_airline_date_idx');
            });
        }

        if (! Schema::hasTable('olap_route_daily_summaries')) {
            Schema::create('olap_route_daily_summaries', function (Blueprint $table) {
                $table->id();
                $table->date('summary_date');
                $table->unsignedBigInteger('departure_airport_id')->nullable();
                $table->unsignedBigInteger('arrival_airport_id')->nullable();
                $table->unsignedInteger('flight_count')->default(0);
                $table->unsignedInteger('paid_transaction_count')->default(0);
                $table->unsignedInteger('passenger_count')->default(0);
                $table->bigInteger('revenue_sum')->default(0);
                $table->timestamps();

                $table->unique(['summary_date', 'departure_airport_id', 'arrival_airport_id'], 'olap_route_daily_unique');
                $table->index(['departure_airport_id', 'arrival_airport_id'], 'olap_route_airports_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('olap_route_daily_summaries');
        Schema::dropIfExists('olap_transaction_daily_summaries');

        $uniqueIndexes = [
            ['airports', 'airports_iata_code_unique_norm'],
            ['airlines', 'airlines_iata_code_unique_norm'],
            ['flight_segments', 'flight_segments_flight_sequence_unique_norm'],
            ['flight_classes', 'flight_classes_flight_type_unique_norm'],
            ['flight_seats', 'flight_seats_flight_name_unique_norm'],
            ['flight_class_facilty', 'flight_class_facilty_pair_unique_norm'],
        ];

        foreach ($uniqueIndexes as [$tableName, $indexName]) {
            $this->dropIndexIfExists($tableName, $indexName);
        }

        foreach ($this->indexes as $tableName => $indexes) {
            foreach ($indexes as $index) {
                $this->dropIndexIfExists($tableName, $index['name']);
            }
        }
    }

    private function addUniqueIndexIfClean(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->columnsExist($tableName, $columns) || $this->indexExists($tableName, $indexName)) {
            return;
        }

        $columnList = collect($columns)->map(fn (string $column) => "`{$column}`")->implode(', ');
        $duplicate = DB::table($tableName)
            ->selectRaw("{$columnList}, COUNT(*) as aggregate")
            ->whereNull('deleted_at')
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicate) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->unique($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    /*
     * PERUBAHAN: Cek index menggunakan Schema::getIndexes() agar kompatibel 
     * dengan database SQLite (in-memory) saat testing & MySQL saat produksi.
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }

        return collect(Schema::getIndexes($tableName))->pluck('name')->contains($indexName);
    }

    private function columnsExist(string $tableName, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }
};
