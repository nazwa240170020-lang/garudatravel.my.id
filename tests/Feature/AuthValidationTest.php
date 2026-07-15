<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_empty_fields_shows_errors(): void
    {
        $response = $this->from('/login')->post('/login', []);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_login_with_nonexistent_email_stays_on_login(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'tidakada@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_register_with_empty_fields_shows_errors(): void
    {
        $response = $this->from('/register')->post('/register', []);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_register_with_password_mismatch_shows_errors(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);
    }

    public function test_register_with_duplicate_email_shows_errors(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_flights_page_accessible_without_auth(): void
    {
        $response = $this->get('/flights');

        $response->assertStatus(200);
    }

    public function test_my_bookings_redirects_guest(): void
    {
        $response = $this->get('/my-bookings');

        $response->assertRedirect('/login');
    }

    public function test_booking_create_redirects_guest(): void
    {
        $response = $this->get('/booking/create');

        $response->assertRedirect('/login');
    }
}
