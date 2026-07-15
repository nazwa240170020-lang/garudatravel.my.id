<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['iata_code' => 'GA', 'name' => 'Garuda Indonesia', 'logo' => 'airlines/garuda-indonesia.png'],
            ['iata_code' => 'QG', 'name' => 'Citilink', 'logo' => 'airlines/citilink.png'],
            ['iata_code' => 'ID', 'name' => 'Batik Air', 'logo' => 'airlines/batik-air.png'],
            ['iata_code' => 'JT', 'name' => 'Lion Air', 'logo' => 'airlines/lion-air.png'],
            ['iata_code' => 'SQ', 'name' => 'Singapore Airlines', 'logo' => 'airlines/singapore-airlines.png'],
            ['iata_code' => 'AK', 'name' => 'Indonesia AirAsia', 'logo' => 'airlines/airasia.png'],
            ['iata_code' => 'SJ', 'name' => 'Sriwijaya Air', 'logo' => 'airlines/sriwijaya.png'],
        ];

        foreach ($rows as $row) {
            Airline::withTrashed()->updateOrCreate(
                ['iata_code' => $row['iata_code']],
                array_merge($row, ['deleted_at' => null])
            );
        }
    }
}
