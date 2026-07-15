<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    protected $model = Flight::class;

    public function definition(): array
    {
        return [
            'flight_number' => strtoupper(fake()->bothify('??###')),
            'airline_id' => Airline::factory(),
        ];
    }
}
