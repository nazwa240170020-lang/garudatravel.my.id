<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendRouteTest extends TestCase
{
    use RefreshDatabase;

    /*
     * PERUBAHAN: Mengubah assertion test halaman welcome (/) dari redirects (302) 
     * menjadi status 200 OK karena / sekarang memuat landing page publik secara langsung.
     */
    public function test_welcome_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_dashboard_renders_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_flights_search_renders_successfully(): void
    {
        $user = User::factory()->create();
        $airport1 = Airport::create([
            'iata_code' => 'CGK',
            'name' => 'Soekarno-Hatta',
            'image' => 'airports/cgk.png',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
        ]);
        $airport2 = Airport::create([
            'iata_code' => 'DPS',
            'name' => 'Ngurah Rai',
            'image' => 'airports/dps.png',
            'city' => 'Bali',
            'country' => 'Indonesia',
        ]);

        $response = $this->actingAs($user)->get('/flights?departure_id=' . $airport1->id . '&arrival_id=' . $airport2->id);

        $response->assertStatus(200);
    }

    public function test_choose_tier_renders_successfully(): void
    {
        $user = User::factory()->create();
        $flight = Flight::factory()->create();

        $response = $this->actingAs($user)->get("/flight/{$flight->flight_number}/choose-tier");

        $response->assertStatus(200);
    }

    public function test_choose_seat_renders_successfully(): void
    {
        $user = User::factory()->create();
        $flight = Flight::factory()->create();
        $flightClass = FlightClass::factory()->create([
            'flight_id' => $flight->id,
            'class_type' => 'economy',
        ]);

        $response = $this->actingAs($user)->get("/flight/{$flight->flight_number}/booking/{$flightClass->id}/choose-seat?passengers=1");

        $response->assertStatus(200);
    }

    public function test_booking_create_form_renders_successfully(): void
    {
        $user = User::factory()->create();
        $flight = Flight::factory()->create();
        $flightClass = FlightClass::factory()->create([
            'flight_id' => $flight->id,
            'class_type' => 'economy',
        ]);
        FlightSeat::factory()->create([
            'flight_id' => $flight->id,
            'class_type' => 'economy',
            'name' => '12A',
        ]);

        $response = $this->actingAs($user)->get("/booking/create?flight_id={$flight->id}&flight_class_id={$flightClass->id}&passengers=1&seats=12A");

        $response->assertStatus(200);
    }

    public function test_checkout_page_returns_not_found_after_removal(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $transaction = Transaction::factory()->create(['email' => 'owner@example.com']);

        $response = $this->actingAs($user)->get("/booking/{$transaction->id}/checkout");

        $response->assertStatus(404);
    }

    public function test_payment_page_is_authorized_for_owner(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $transaction = Transaction::factory()->create([
            'email' => 'owner@example.com',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get("/booking/{$transaction->id}/payment");

        $response->assertStatus(200);
    }

    public function test_payment_page_is_forbidden_for_non_owner(): void
    {
        $user = User::factory()->create(['email' => 'stranger@example.com']);
        $transaction = Transaction::factory()->create([
            'email' => 'owner@example.com',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get("/booking/{$transaction->id}/payment");

        $response->assertStatus(403);
    }

    public function test_booking_detail_page_is_authorized_for_owner(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $transaction = Transaction::factory()->create(['email' => 'owner@example.com']);

        $response = $this->actingAs($user)->get("/booking/{$transaction->id}");

        $response->assertStatus(200);
    }

    public function test_booking_detail_page_is_forbidden_for_non_owner(): void
    {
        $user = User::factory()->create(['email' => 'stranger@example.com']);
        $transaction = Transaction::factory()->create(['email' => 'owner@example.com']);

        $response = $this->actingAs($user)->get("/booking/{$transaction->id}");

        $response->assertStatus(403);
    }
}
