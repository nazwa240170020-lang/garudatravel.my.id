<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach ($this->alterations() as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column => $rawType) {
                if (Schema::hasColumn($table, $column)) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$rawType}");
                }
            }
        }
    }

    public function down(): void
    {
    }

    private function alterations(): array
    {
        return [
            'flight_segments' => [
                'sequence' => 'INT UNSIGNED NOT NULL',
            ],
            'flight_classes' => [
                'price' => 'INT UNSIGNED NOT NULL',
                'total_seats' => 'INT UNSIGNED NOT NULL',
            ],
            'transactions' => [
                'number_of_passengers' => 'INT UNSIGNED NOT NULL',
                'subtotal' => 'BIGINT UNSIGNED DEFAULT NULL',
                'discount' => 'BIGINT UNSIGNED NOT NULL DEFAULT 0',
                'grandtotal' => 'BIGINT UNSIGNED DEFAULT NULL',
            ],
            'promo_codes' => [
                'discount' => 'BIGINT UNSIGNED NOT NULL',
            ],
        ];
    }
};
