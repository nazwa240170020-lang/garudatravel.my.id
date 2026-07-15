<?php

namespace Database\Seeders;

use App\Models\Facilty;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Bagasi Ekstra', 'image' => 'facilties/extra-baggage.png', 'description' => 'Kuota bagasi tambahan untuk perjalanan jauh.'],
            ['name' => 'Prioritas Boarding', 'image' => 'facilties/priority-boarding.png', 'description' => 'Antrean masuk pesawat lebih cepat dan nyaman.'],
            ['name' => 'Makanan di Pesawat', 'image' => 'facilties/in-flight-meal.png', 'description' => 'Hidangan makanan dan minuman gratis selama penerbangan.'],
            ['name' => 'Akses Lounge', 'image' => 'facilties/lounge-access.png', 'description' => 'Akses ke lounge bandara sebelum lepas landas.'],
            ['name' => 'Reschedule Fleksibel', 'image' => 'facilties/flexible-reschedule.png', 'description' => 'Kemudahan untuk mengubah jadwal penerbangan Anda.'],
            ['name' => 'Wi-Fi Penerbangan', 'image' => 'facilties/wifi.png', 'description' => 'Koneksi internet nirkabel selama penerbangan.'],
        ];

        foreach ($rows as $row) {
            Facilty::withTrashed()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['deleted_at' => null])
            );
        }
    }
}
