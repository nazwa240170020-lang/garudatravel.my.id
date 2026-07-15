<?php

namespace Database\Seeders;

use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\TransactionPassenger;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        if ($users->isEmpty()) {
            return;
        }

        $flights = Flight::with(['classes', 'seats'])->get();
        if ($flights->isEmpty()) {
            return;
        }

        $promos = PromoCode::all();

        $paymentStatuses = ['paid', 'paid', 'paid', 'paid', 'paid', 'pending', 'failed']; // mostly paid
        $paymentMethods = ['midtrans', 'bank_transfer', 'credit_card'];
        $paymentChannels = [
            'midtrans' => ['gopay', 'shopeepay', 'qris'],
            'bank_transfer' => ['bca', 'mandiri', 'bni'],
            'credit_card' => ['visa', 'mastercard']
        ];

        $names = [
            'Budi Santoso', 'Siti Aminah', 'Rudi Hermawan', 'Dewi Lestari', 'Agus Wijaya',
            'Mega Rahayu', 'Eko Prasetyo', 'Lia Natalia', 'Hadi Sucipto', 'Yuni Kartika',
            'Andi Pratama', 'Rina Wulandari', 'Dedi Setiawan', 'Sari Indah', 'Fajar Ramadhan',
            'Gita Permata', 'Joko Susilo', 'Novianti', 'Bambang Utomo', 'Sri Wahyuni',
            'Kurniawan', 'Kartika Sari', 'Pratama Putra', 'Fitriani', 'Aditya Nugraha',
            'Nila Kusuma', 'Rian Hidayat', 'Putri Utami', 'Rizky Fauzi', 'Indah Permata'
        ];

        for ($i = 1; $i <= 150; $i++) {
            $date = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $flight = $flights->random();
            
            $classes = $flight->classes;
            if ($classes->isEmpty()) {
                continue;
            }
            $flightClass = $classes->random();
            $classType = $flightClass->class_type;
            
            $customer = $users->random();
            $status = $paymentStatuses[array_rand($paymentStatuses)];
            
            $passengerCount = rand(1, 3);
            $passengerNames = [];
            for ($p = 0; $p < $passengerCount; $p++) {
                $passengerNames[] = $names[array_rand($names)];
            }
            
            $promo = (rand(1, 100) <= 25 && !$promos->isEmpty()) ? $promos->random() : null;
            
            $method = $paymentMethods[array_rand($paymentMethods)];
            $channel = $paymentChannels[$method][array_rand($paymentChannels[$method])];
            
            $paidAt = $status === 'paid' ? $date->copy()->addMinutes(rand(5, 60)) : null;
            
            $code = 'GRD-' . str_pad($i, 5, '0', STR_PAD_LEFT);
            
            $this->createHistoricalTransaction(
                code: $code,
                flight: $flight,
                flightClass: $flightClass,
                classType: $classType,
                customer: $customer,
                passengerNames: $passengerNames,
                paymentStatus: $status,
                promo: $promo,
                paymentMethod: $method,
                paymentChannel: $channel,
                paidAt: $paidAt,
                createdAt: $date
            );
        }
    }

    private function createHistoricalTransaction(
        string $code,
        Flight $flight,
        FlightClass $flightClass,
        string $classType,
        User $customer,
        array $passengerNames,
        string $paymentStatus,
        ?PromoCode $promo = null,
        ?string $paymentMethod = null,
        ?string $paymentChannel = null,
        mixed $paidAt = null,
        mixed $createdAt = null
    ): Transaction {
        $passengerCount = count($passengerNames);
        $subtotal = $flightClass->price * $passengerCount;
        $tax = (int) round($subtotal * 0.11);
        $discount = $promo
            ? ($promo->discount_type === 'percentage'
                ? (int) round(($subtotal + $tax) * $promo->discount / 100)
                : (int) $promo->discount)
            : 0;
        $grandtotal = max($subtotal + $tax - $discount, 0);

        $transaction = Transaction::withTrashed()->updateOrCreate(
            ['code' => $code],
            [
                'user_id' => $customer->id,
                'flight_id' => $flight->id,
                'flight_class_id' => $flightClass->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => '08123456' . rand(1000, 9999),
                'number_of_passengers' => $passengerCount,
                'promo_code_id' => $promo?->id,
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grandtotal' => $grandtotal,
                'paid_at' => $paidAt,
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'deleted_at' => null,
                'created_at' => $createdAt ?? now(),
                'updated_at' => $createdAt ?? now(),
            ]
        );

        $availableSeats = FlightSeat::where('flight_id', $flight->id)
            ->where('class_type', $classType)
            ->orderBy('row')
            ->orderBy('column')
            ->limit($passengerCount)
            ->get();

        foreach ($passengerNames as $index => $name) {
            if (!isset($availableSeats[$index])) {
                $flight->generateSeats(10, 6, $classType);
                $availableSeats = FlightSeat::where('flight_id', $flight->id)
                    ->where('class_type', $classType)
                    ->orderBy('row')
                    ->orderBy('column')
                    ->get();
            }
            
            TransactionPassenger::withTrashed()->updateOrCreate(
                ['transaction_id' => $transaction->id, 'name' => $name],
                [
                    'flight_seat_id' => $availableSeats[$index]->id,
                    'date_of_birth' => now()->subYears(20 + rand(0, 30))->toDateString(),
                    'nationality' => 'Indonesia',
                    'deleted_at' => null,
                    'created_at' => $createdAt ?? now(),
                    'updated_at' => $createdAt ?? now(),
                ]
            );
        }

        return $transaction;
    }
}
