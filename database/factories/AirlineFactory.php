<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirlineFactory extends Factory
{
    protected $model = Airline::class;

    /**
     * Menentukan nilai bawaan (default) untuk factory Maskapai (Airline).
     * 
     * PERUBAHAN DARI KODE ASLI:
     * Menambahkan key `'logo' => 'airlines/logo.png'` karena kolom `logo` pada tabel maskapai 
     * bersifat NOT NULL di migration, namun bawaan asli pabrik (factory) tidak mendefinisikannya, 
     * sehingga memicu error constraint saat seeder/testing dijalankan.
     */
    public function definition(): array
    {
        return [
            'iata_code' => strtoupper(fake()->lexify('??')),
            'name' => fake()->company(),
            'logo' => 'airlines/logo.png',
        ];
    }
}
