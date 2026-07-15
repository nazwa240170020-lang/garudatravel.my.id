<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\FlightSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class FlightSeatFactory
 * 
 * FILE BARU: Factory untuk model FlightSeat (Kursi Penerbangan).
 * Digunakan untuk mendukung pengujian (unit & feature testing) dalam membuat data dummy kursi penerbangan, 
 * khususnya untuk pengujian fitur pemesanan massal (bulk insert) data penumpang.
 */
class FlightSeatFactory extends Factory
{
    protected $model = FlightSeat::class;

    public function definition(): array
    {
        return [
            'flight_id' => Flight::factory(),
            'name' => fake()->randomLetter() . fake()->numberBetween(1, 30),
            'row' => fake()->numberBetween(1, 30),
            'column' => fake()->randomLetter(),
            'is_available' => true,
            'class_type' => 'economy',
        ];
    }
}
