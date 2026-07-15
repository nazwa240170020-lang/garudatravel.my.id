<?php

namespace Tests\Feature;

/*
 * PERUBAHAN UNTUK UX STANDAR:
 * 1. Mengaktifkan trait RefreshDatabase agar in-memory database migrasi otomatis saat testing.
 * 2. Mengubah assertion ke status 200 karena root (/) sekarang memuat halaman welcome secara publik (bukan redirect).
 */
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
