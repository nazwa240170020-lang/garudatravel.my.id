<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('promo_codes', 'is_active')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }

        if (! Schema::hasColumn('promo_codes', 'usage_limit')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                $table->unsignedInteger('usage_limit')->nullable();
            });
        }

        if (! Schema::hasColumn('promo_codes', 'used_count')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                $table->unsignedInteger('used_count')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'usage_limit', 'used_count']);
        });
    }
};
