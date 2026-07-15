<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists('users', 'users_email_lookup_idx')) {
            Schema::table('users', function ($table) {
                $table->dropIndex('users_email_lookup_idx');
            });
        }
    }

    public function down(): void
    {
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }
        return collect(Schema::getIndexes($tableName))->pluck('name')->contains($indexName);
    }
};
