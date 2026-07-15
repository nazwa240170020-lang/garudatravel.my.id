<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\Facilty;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\TransactionPassenger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class BackendFixTest
 * 
 * Unit Testing Suite untuk memverifikasi fungsionalitas backend dan logika bisnis yang diperbaiki.
 */
class BackendFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan kolom fillable model Transaction mencakup kolom-kolom baru.
     */
    public function test_transaction_fillable_includes_new_columns(): void
    {
        $expected = [
            'user_id', 'code', 'flight_id', 'flight_class_id', 'name', 'email', 'phone',
            'number_of_passengers', 'promo_code_id', 'payment_status',
            'subtotal', 'discount', 'grandtotal', 'paid_at', 'payment_method', 'payment_channel',
            'mail_sent_at', 'snap_token',
        ];

        $model = new Transaction();

        $this->assertEquals($expected, $model->getFillable());
    }

    /**
     * Memastikan data transaksi dapat disimpan lengkap dengan diskon.
     */
    public function test_transaction_can_be_created_with_discount(): void
    {
        $transaction = Transaction::factory()->create([
            'discount' => 50000,
            'subtotal' => 500000,
            'grandtotal' => 450000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'discount' => 50000,
            'grandtotal' => 450000,
        ]);
    }

    /**
     * Memastikan kolom log metode dan status pembayaran dapat tersimpan.
     */
    public function test_transaction_can_store_payment_columns(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'midtrans',
            'payment_channel' => 'gopay',
        ]);

        $this->assertNotNull($transaction->paid_at);
        $this->assertEquals('midtrans', $transaction->payment_method);
        $this->assertEquals('gopay', $transaction->payment_channel);
    }

    /**
     * Memastikan nama tabel pivot pada relasi Facilty adalah flight_class_facilty (bukan typo filgh_class_facilty).
     */
    public function test_facilty_pivot_table_name_is_correct(): void
    {
        $facilty = new Facilty();
        $relation = $facilty->classes();

        $this->assertEquals('flight_class_facilty', $relation->getTable());
    }

    /**
     * Memastikan pembuatan kursi otomatis (generate seats) menghasilkan jumlah kursi yang tepat.
     */
    public function test_flight_generate_seats_creates_correct_number_of_seats(): void
    {
        $flight = Flight::factory()->create();

        $flight->generateSeats(
            totalSeats: 12,
            seatsPerRow: 6,
            classType: 'economy'
        );

        $seats = FlightSeat::where('flight_id', $flight->id)
            ->where('class_type', 'economy')
            ->get();

        $this->assertCount(12, $seats);
    }

    /**
     * Memastikan pembuatan kursi menghasilkan kode kursi yang unik.
     */
    public function test_flight_generate_seats_creates_unique_seat_codes(): void
    {
        $flight = Flight::factory()->create();

        $flight->generateSeats(
            totalSeats: 30,
            seatsPerRow: 6,
            classType: 'business'
        );

        $seatCodes = FlightSeat::where('flight_id', $flight->id)
            ->pluck('name')
            ->toArray();

        $this->assertCount(30, array_unique($seatCodes));
    }

    /**
     * Memastikan penyimpanan massal (bulk insert) data penumpang berfungsi dengan benar.
     */
    public function test_transaction_passengers_can_be_inserted_in_bulk(): void
    {
        $transaction = Transaction::factory()->create();
        $seat = FlightSeat::factory()->create([
            'flight_id' => $transaction->flight_id,
        ]);

        $passengerData = [
            [
                'transaction_id' => $transaction->id,
                'flight_seat_id' => $seat->id,
                'name' => 'John Doe',
                'date_of_birth' => '1990-01-01',
                'nationality' => 'Indonesia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_id' => $transaction->id,
                'flight_seat_id' => $seat->id,
                'name' => 'Jane Doe',
                'date_of_birth' => '1992-05-15',
                'nationality' => 'Indonesia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        TransactionPassenger::insert($passengerData);

        $this->assertDatabaseCount('transaction_passengers', 2);
        $this->assertDatabaseHas('transaction_passengers', ['name' => 'John Doe']);
        $this->assertDatabaseHas('transaction_passengers', ['name' => 'Jane Doe']);
    }

    /**
     * Memastikan model Transaction memiliki relasi bernama 'class'.
     */
    public function test_transaction_class_relationship_name_is_correct(): void
    {
        $transaction = new Transaction();

        $this->assertTrue(method_exists($transaction, 'class'));
    }

    /**
     * Memastikan relasi 'class' pada Transaction dapat di eager-load.
     */
    public function test_transaction_can_load_class_relationship(): void
    {
        $transaction = Transaction::factory()->create();

        $loaded = $transaction->load(['passengers.seat', 'flight.segments.airport', 'class']);

        $this->assertTrue($loaded->relationLoaded('class'));
    }

    /**
     * Memastikan model relasi target pada Facilty::classes() mengarah ke FlightClass (bukan Flight).
     */
    public function test_facilty_relates_to_flight_class(): void
    {
        $facilty = new Facilty();
        $relation = $facilty->classes();

        $this->assertEquals(FlightClass::class, get_class($relation->getRelated()));
    }

    /**
     * Memastikan TransactionPolicy berfungsi menghalangi akses ilegal dan mengizinkan akses bagi pemilik/admin.
     */
    public function test_transaction_policy_authorizes_correctly(): void
    {
        $policy = new \App\Policies\TransactionPolicy();

        $owner = \App\Models\User::factory()->create(['email' => 'owner@example.com']);
        $stranger = \App\Models\User::factory()->create(['email' => 'stranger@example.com']);

        $transaction = Transaction::factory()->create(['email' => 'owner@example.com']);

        $this->assertTrue($policy->view($owner, $transaction));
        $this->assertFalse($policy->view($stranger, $transaction));
    }
}
