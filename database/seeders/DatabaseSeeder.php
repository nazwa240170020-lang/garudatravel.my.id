<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AirportSeeder::class,
            AirlineSeeder::class,
            FacilitySeeder::class,
            PromoCodeSeeder::class,
            FlightSeeder::class,
            TransactionSeeder::class,
            SiteSectionSeeder::class,
        ]);

        try {
            Artisan::call('olap:refresh');
        } catch (\Exception $e) {
            $this->command->warn('OLAP refresh skipped: ' . $e->getMessage());
        }
    }
}
