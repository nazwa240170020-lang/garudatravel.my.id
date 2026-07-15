<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $flight = Flight::factory()->create();
        $flightClass = FlightClass::factory()->create(['flight_id' => $flight->id]);

        return [
            'user_id' => User::factory(),
            'code' => 'GRD-' . strtoupper(fake()->lexify('????????')),
            'flight_id' => $flight->id,
            'flight_class_id' => $flightClass->id,
            'name' => fake()->name(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'number_of_passengers' => 1,
            'payment_status' => 'pending',
            'subtotal' => 500000,
            'grandtotal' => 500000,
        ];
    }
}
