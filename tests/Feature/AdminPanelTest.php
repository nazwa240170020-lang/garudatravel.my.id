<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_login_page_renders(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_guest_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_dashboard_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_airlines_list_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/airlines');

        $response->assertStatus(200);
    }

    public function test_airline_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/airlines/create');

        $response->assertStatus(200);
    }

    public function test_airports_list_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/airports');

        $response->assertStatus(200);
    }

    public function test_airport_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/airports/create');

        $response->assertStatus(200);
    }

    public function test_promo_codes_list_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/promo-codes');

        $response->assertStatus(200);
    }

    public function test_promo_code_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/promo-codes/create');

        $response->assertStatus(200);
    }
}
