<?php

namespace Tests\Feature;

use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\TransactionPassenger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Flight $flight;
    private FlightClass $flightClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->flight = Flight::factory()->create();

        $this->flightClass = FlightClass::factory()->create([
            'flight_id' => $this->flight->id,
            'class_type' => 'economy',
            'price' => 500000,
            'total_seats' => 60,
        ]);
    }

    private function createSeats(string $classType, int $count = 10): \Illuminate\Support\Collection
    {
        $seats = collect();
        for ($i = 1; $i <= $count; $i++) {
            $row = (int) ceil($i / 6);
            $col = (($i - 1) % 6) + 1;
            $letter = chr(64 + $col);
            $seats->push(FlightSeat::create([
                'flight_id' => $this->flight->id,
                'class_type' => $classType,
                'row' => $row,
                'column' => $col,
                'name' => $row . $letter,
                'is_available' => true,
            ]));
        }
        return $seats;
    }

    public function test_create_form_renders_with_valid_parameters(): void
    {
        $seats = $this->createSeats('economy', 2);

        $response = $this->actingAs($this->user)->get('/booking/create?' . http_build_query([
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->flightClass->id,
            'passengers' => 1,
            'seats' => $seats->first()->name,
        ]));

        $response->assertStatus(200);
    }

    public function test_create_form_requires_authentication(): void
    {
        $response = $this->get('/booking/create');

        $response->assertRedirect('/login');
    }

    public function test_store_creates_transaction_successfully(): void
    {
        $seats = $this->createSeats('economy', 1);

        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->flightClass->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'number_of_passengers' => 1,
            'passengers' => [
                [
                    'seat_id' => $seats->first()->id,
                    'name' => 'John Doe',
                    'dob' => '1990-01-15',
                    'nationality' => 'Indonesia',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, Transaction::count());
        $this->assertEquals(1, TransactionPassenger::count());
    }

    public function test_store_rejects_passenger_count_mismatch(): void
    {
        $seats = $this->createSeats('economy', 2);

        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->flightClass->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'number_of_passengers' => 2,
            'passengers' => [
                [
                    'seat_id' => $seats[0]->id,
                    'name' => 'John Doe',
                    'dob' => '1990-01-15',
                    'nationality' => 'Indonesia',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Transaction::count());
    }

    public function test_store_with_valid_promo_applies_discount(): void
    {
        $promo = PromoCode::create([
            'code' => 'TEST10',
            'discount_type' => 'percentage',
            'discount' => 10,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $seats = $this->createSeats('economy', 1);

        $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->flightClass->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'number_of_passengers' => 1,
            'promo_code' => 'TEST10',
            'passengers' => [
                [
                    'seat_id' => $seats->first()->id,
                    'name' => 'John Doe',
                    'dob' => '1990-01-15',
                    'nationality' => 'Indonesia',
                ],
            ],
        ]);

        $transaction = Transaction::first();
        $this->assertNotNull($transaction);
        $this->assertGreaterThan(0, $transaction->discount);
        $this->assertEquals($promo->id, $transaction->promo_code_id);
    }

    public function test_store_rejects_already_booked_seats(): void
    {
        $seats = $this->createSeats('economy', 1);
        $existingTransaction = Transaction::factory()->create([
            'flight_id' => $this->flight->id,
            'payment_status' => 'paid',
        ]);
        TransactionPassenger::create([
            'transaction_id' => $existingTransaction->id,
            'flight_seat_id' => $seats->first()->id,
            'name' => 'Existing Passenger',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'Indonesia',
        ]);

        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->flightClass->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'number_of_passengers' => 1,
            'passengers' => [
                [
                    'seat_id' => $seats->first()->id,
                    'name' => 'John Doe',
                    'dob' => '1990-01-15',
                    'nationality' => 'Indonesia',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Transaction::count());
    }

    public function test_ajax_seats_returns_available_seats(): void
    {
        $this->createSeats('economy', 3);

        $response = $this->actingAs($this->user)
            ->getJson('/ajax/seats?flight_id=' . $this->flight->id . '&class_type=economy');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'seats' => [
                    '*' => ['id', 'name', 'available', 'row', 'column'],
                ],
            ]);
    }

    public function test_my_bookings_shows_only_user_transactions(): void
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
        ]);
        Transaction::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->user)->get('/my-bookings');

        $response->assertStatus(200);
        $response->assertViewHas('transactions');
    }

    public function test_check_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/booking/check');

        $response->assertStatus(200);
    }

    public function test_check_with_valid_code_redirects_to_detail(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/booking/check?code=' . $transaction->code);

        $response->assertRedirect(route('booking.detail', $transaction->id));
    }

    public function test_check_with_invalid_code_shows_error(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/booking/check?code=INVALID');

        $response->assertSessionHas('error');
    }

    public function test_detail_page_shows_for_owner(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/booking/' . $transaction->id);

        $response->assertStatus(200);
    }

    public function test_detail_page_forbidden_for_stranger(): void
    {
        $stranger = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->actingAs($stranger)
            ->get('/booking/' . $transaction->id);

        $response->assertStatus(403);
    }

    public function test_ajax_payment_status_returns_pending(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/ajax/payment-status/' . $transaction->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'pending',
            ]);
    }

    public function test_promo_check_endpoint_validates_code(): void
    {
        PromoCode::create([
            'code' => 'PROMO99',
            'discount_type' => 'fixed',
            'discount' => 50000,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/promo/check?code=promo99');

        $response->assertStatus(200)
            ->assertJson(['valid' => true]);
    }

    public function test_promo_check_endpoint_rejects_invalid_code(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/promo/check?code=INVALID');

        $response->assertStatus(200)
            ->assertJson(['valid' => false]);
    }
}
