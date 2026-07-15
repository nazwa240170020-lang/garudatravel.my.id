<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['iata_code' => 'CGK', 'name' => 'Soekarno-Hatta International Airport', 'image' => 'airports/cgk.jpg', 'city' => 'Jakarta', 'country' => 'Indonesia'],
            ['iata_code' => 'DPS', 'name' => 'I Gusti Ngurah Rai International Airport', 'image' => 'airports/dps.jpg', 'city' => 'Bali', 'country' => 'Indonesia'],
            ['iata_code' => 'SUB', 'name' => 'Juanda International Airport', 'image' => 'airports/sub.jpg', 'city' => 'Surabaya', 'country' => 'Indonesia'],
            ['iata_code' => 'KNO', 'name' => 'Kualanamu International Airport', 'image' => 'airports/kno.jpg', 'city' => 'Medan', 'country' => 'Indonesia'],
            ['iata_code' => 'UPG', 'name' => 'Sultan Hasanuddin International Airport', 'image' => 'airports/upg.jpg', 'city' => 'Makassar', 'country' => 'Indonesia'],
            ['iata_code' => 'YIA', 'name' => 'Yogyakarta International Airport', 'image' => 'airports/yia.jpg', 'city' => 'Yogyakarta', 'country' => 'Indonesia'],
            ['iata_code' => 'BPN', 'name' => 'Sultan Aji Muhammad Sulaiman Sepinggan Airport', 'image' => 'airports/bpn.jpg', 'city' => 'Balikpapan', 'country' => 'Indonesia'],
            ['iata_code' => 'HLP', 'name' => 'Halim Perdanakusuma International Airport', 'image' => 'airports/hlp.jpg', 'city' => 'Jakarta (Halim)', 'country' => 'Indonesia'],
            ['iata_code' => 'LOP', 'name' => 'Zainuddin Abdul Madjid International Airport', 'image' => 'airports/lop.jpg', 'city' => 'Lombok', 'country' => 'Indonesia'],
            ['iata_code' => 'SRG', 'name' => 'Ahmad Yani International Airport', 'image' => 'airports/srg.jpg', 'city' => 'Semarang', 'country' => 'Indonesia'],
            ['iata_code' => 'BDG', 'name' => 'Husein Sastranegara Airport', 'image' => 'airports/bdg.jpg', 'city' => 'Bandung', 'country' => 'Indonesia'],
            ['iata_code' => 'SIN', 'name' => 'Changi International Airport', 'image' => 'airports/sin.jpg', 'city' => 'Singapore', 'country' => 'Singapore'],
            ['iata_code' => 'KUL', 'name' => 'Kuala Lumpur International Airport', 'image' => 'airports/kul.jpg', 'city' => 'Kuala Lumpur', 'country' => 'Malaysia'],
            ['iata_code' => 'HND', 'name' => 'Tokyo Haneda International Airport', 'image' => 'airports/hnd.jpg', 'city' => 'Tokyo', 'country' => 'Japan'],
            ['iata_code' => 'JED', 'name' => 'King Abdulaziz International Airport', 'image' => 'airports/jed.jpg', 'city' => 'Jeddah', 'country' => 'Saudi Arabia'],
        ];

        foreach ($rows as $row) {
            Airport::withTrashed()->updateOrCreate(
                ['iata_code' => $row['iata_code']],
                array_merge($row, ['deleted_at' => null])
            );
        }
    }
}
