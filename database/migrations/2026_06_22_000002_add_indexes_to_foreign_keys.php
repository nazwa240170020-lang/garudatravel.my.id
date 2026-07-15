<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'flights'                  => ['airline_id'],
        'flight_segments'          => ['flight_id', 'airport_id'],
        'flight_classes'           => ['flight_id'],
        'flight_seats'             => ['flight_id'],
        'flight_class_facilty'     => ['flight_class_id', 'facilty_id'],
        'transactions'             => ['flight_id', 'flight_class_id', 'promo_code_id'],
        'transaction_passengers'   => ['transaction_id', 'flight_seat_id'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $t) use ($tableName, $columns) {
                foreach ($columns as $col) {
                    $indexName = "{$tableName}_{$col}_idx";
                    if (! $this->indexExists($tableName, $indexName)) {
                        $t->index($col, $indexName);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $t) use ($tableName, $columns) {
                foreach ($columns as $col) {
                    $indexName = "{$tableName}_{$col}_idx";
                    if ($this->indexExists($tableName, $indexName)) {
                        $t->dropIndex($indexName);
                    }
                }
            });
        }
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
};
