<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists('transactions', 'transactions_payment_status_index')) {
            Schema::table('transactions', function ($table) {
                $table->dropIndex('transactions_payment_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function ($table) {
            $table->index('payment_status', 'transactions_payment_status_index');
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }
        return collect(Schema::getIndexes($tableName))->pluck('name')->contains($indexName);
    }
};
