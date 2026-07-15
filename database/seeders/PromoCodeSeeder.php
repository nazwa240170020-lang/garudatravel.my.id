<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'GARUDA10', 'discount_type' => 'percentage', 'discount' => 10, 'valid_until' => now()->addMonths(3), 'is_active' => true],
            ['code' => 'HEMAT50K', 'discount_type' => 'fixed', 'discount' => 50000, 'valid_until' => now()->addMonths(2), 'is_active' => true],
            ['code' => 'BUSINESS15', 'discount_type' => 'percentage', 'discount' => 15, 'valid_until' => now()->addMonth(), 'is_active' => true],
        ];

        foreach ($rows as $row) {
            PromoCode::withTrashed()->updateOrCreate(
                ['code' => $row['code']],
                array_merge($row, ['deleted_at' => null])
            );
        }
    }
}
