<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\FlightClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightClassFactory extends Factory
{
    protected $model = FlightClass::class;

    public function definition(): array
    {
        return [
            'class_type' => fake()->randomElement(['economy', 'business']),
            'price' => fake()->numberBetween(200000, 2000000),
            'total_seats' => 60,
        ];
    }

    public function forFlight(Flight $flight): static
    {
        return $this->state(fn (array $attributes) => [
            'flight_id' => $flight->id,
        ]);
    }
}
