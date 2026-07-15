<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Facilty;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\FlightSegment;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgenticBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Flight $flight;
    private FlightClass $economyClass;
    private FlightClass $businessClass;
    private FlightSeat $seat;
    private PromoCode $promo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Ayu Traveler',
            'email' => 'ayu@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $airline = Airline::create([
            'iata_code' => 'GA',
            'name' => 'Garuda Indonesia',
            'logo' => 'airlines/ga.png',
        ]);

        $cgk = Airport::create([
            'iata_code' => 'CGK', 'name' => 'Soekarno-Hatta',
            'city' => 'Jakarta', 'country' => 'Indonesia',
            'image' => 'airports/cgk.jpg',
        ]);

        $dps = Airport::create([
            'iata_code' => 'DPS', 'name' => 'Ngurah Rai',
            'city' => 'Bali', 'country' => 'Indonesia',
            'image' => 'airports/dps.jpg',
        ]);

        $this->flight = Flight::create([
            'flight_number' => 'GA-401',
            'airline_id' => $airline->id,
        ]);

        FlightSegment::create(['flight_id' => $this->flight->id, 'sequence' => 1, 'airport_id' => $cgk->id, 'time' => now()->addDay()->setTime(8, 30)]);
        FlightSegment::create(['flight_id' => $this->flight->id, 'sequence' => 2, 'airport_id' => $dps->id, 'time' => now()->addDay()->setTime(11, 15)]);

        $this->economyClass = FlightClass::create([
            'flight_id' => $this->flight->id,
            'class_type' => 'economy',
            'price' => 1250000,
            'total_seats' => 60,
        ]);

        $this->businessClass = FlightClass::create([
            'flight_id' => $this->flight->id,
            'class_type' => 'business',
            'price' => 3200000,
            'total_seats' => 8,
        ]);

        $facility = Facilty::create([
            'name' => 'In-flight Meal',
            'image' => 'facilties/meal.png',
            'description' => 'Meal service',
        ]);

        $this->economyClass->facilties()->attach($facility->id);
        $this->businessClass->facilties()->attach($facility->id);

        $this->seat = FlightSeat::create([
            'flight_id' => $this->flight->id,
            'name' => 'GA-401-1A',
            'row' => 1,
            'column' => 'A',
            'is_available' => true,
            'class_type' => 'economy',
        ]);

        FlightSeat::create([
            'flight_id' => $this->flight->id,
            'name' => 'GA-401-1B',
            'row' => 1,
            'column' => 'B',
            'is_available' => true,
            'class_type' => 'economy',
        ]);

        $this->promo = PromoCode::create([
            'code' => 'GARUDA10',
            'discount_type' => 'percentage',
            'discount' => 10,
            'valid_until' => now()->addMonth(),
            'is_active' => true,
        ]);
    }

    public function test_agent_lihat_landing_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Garuda');
    }

    public function test_agent_daftar_akun_baru(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Baru',
            'email' => 'budi@baru.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'budi@baru.com']);
    }

    public function test_agent_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'ayu@example.com',
            'password' => 'password',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_agent_login_gagal(): void
    {
        $response = $this->post('/login', [
            'email' => 'ayu@example.com',
            'password' => 'salahpassword',
        ]);
        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_agent_lihat_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Cari Penerbangan');
    }

    public function test_agent_cari_penerbangan(): void
    {
        $response = $this->actingAs($this->user)->get('/flights');
        $response->assertStatus(200);
        $response->assertSee('GA-401');
        $response->assertSee('Economy');
    }

    public function test_agent_cari_penerbangan_dengan_filter(): void
    {
        $departure = Airport::where('iata_code', 'CGK')->first();
        $arrival = Airport::where('iata_code', 'DPS')->first();

        $response = $this->actingAs($this->user)->get('/flights?' . http_build_query([
            'departure_id' => $departure->id,
            'arrival_id' => $arrival->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'passengers' => 2,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Economy Class');
    }

    public function test_agent_pilih_kelas(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/flight/' . $this->flight->flight_number . '/booking/' . $this->economyClass->id . '/choose-seat?passengers=1');
        $response->assertStatus(200);
    }

    public function test_agent_lihat_form_booking(): void
    {
        $response = $this->actingAs($this->user)->get('/booking/create?' . http_build_query([
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->economyClass->id,
            'seat_ids' => [$this->seat->id],
            'passengers' => 1,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Data Pemesan');
    }

    public function test_agent_buat_booking(): void
    {
        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->economyClass->id,
            'seat_ids' => [$this->seat->id],
            'number_of_passengers' => 1,
            'name' => 'Ayu Traveler',
            'email' => 'ayu@example.com',
            'phone' => '08123456789',
            'passengers' => [
                [
                    'name' => 'Ayu Traveler',
                    'dob' => '1995-01-01',
                    'nationality' => 'Indonesia',
                    'seat_id' => $this->seat->id,
                ],
            ],
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_agent_buat_booking_dengan_promo(): void
    {
        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->economyClass->id,
            'seat_ids' => [$this->seat->id],
            'number_of_passengers' => 1,
            'name' => 'Ayu Traveler',
            'email' => 'ayu@example.com',
            'phone' => '08123456789',
            'promo_code' => 'GARUDA10',
            'passengers' => [
                [
                    'name' => 'Ayu Traveler',
                    'dob' => '1995-01-01',
                    'nationality' => 'Indonesia',
                    'seat_id' => $this->seat->id,
                ],
            ],
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_agent_booking_gagal_kursi_dipesan(): void
    {
        $this->seat->update(['is_available' => false]);

        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->economyClass->id,
            'seat_ids' => [$this->seat->id],
            'number_of_passengers' => 1,
            'name' => 'Ayu Traveler',
            'email' => 'ayu@example.com',
            'phone' => '08123456789',
            'passengers' => [
                [
                    'name' => 'Ayu Traveler',
                    'dob' => '1995-01-01',
                    'nationality' => 'Indonesia',
                    'seat_id' => $this->seat->id,
                ],
            ],
        ]);
        $response->assertStatus(302);
    }

    public function test_agent_booking_gagal_jumlah_penumpang(): void
    {
        $response = $this->actingAs($this->user)->post('/booking/store', [
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->economyClass->id,
            'seat_ids' => [$this->seat->id],
            'number_of_passengers' => 2,
            'name' => 'Ayu Traveler',
            'email' => 'ayu@example.com',
            'phone' => '08123456789',
            'passengers' => [
                [
                    'name' => 'Ayu Traveler',
                    'dob' => '1995-01-01',
                    'nationality' => 'Indonesia',
                    'seat_id' => $this->seat->id,
                ],
            ],
        ]);
        $response->assertSessionHas('error');
    }

    public function test_agent_cek_booking_via_kode(): void
    {
        $tx = $this->createSampleTransaction();

        $response = $this->actingAs($this->user)->get('/booking/check?' . http_build_query([
            'code' => $tx->code,
        ]));
        $response->assertStatus(302);
        $response->assertRedirect('/booking/' . $tx->id);
    }

    public function test_agent_cek_booking_kode_salah(): void
    {
        $response = $this->actingAs($this->user)->get('/booking/check?' . http_build_query([
            'code' => 'KODE-SALAH-123',
        ]));
        $response->assertSessionHas('error');
    }

    public function test_agent_lihat_halaman_cek_booking(): void
    {
        $response = $this->actingAs($this->user)->get('/booking/check');
        $response->assertStatus(200);
        $response->assertSee('Cek');
    }

    public function test_agent_lihat_my_bookings(): void
    {
        $this->createSampleTransaction();

        $response = $this->actingAs($this->user)->get('/my-bookings');
        $response->assertStatus(200);
    }

    public function test_agent_lihat_detail_booking(): void
    {
        $tx = $this->createSampleTransaction();

        $response = $this->actingAs($this->user)->get('/booking/' . $tx->id);
        $response->assertStatus(200);
        $response->assertSee($tx->code);
    }

    public function test_agent_tidak_bisa_lihat_booking_orang_lain(): void
    {
        $tx = $this->createSampleTransaction();
        $otherUser = User::create([
            'name' => 'Orang Lain',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)->get('/booking/' . $tx->id);
        $response->assertStatus(403);
    }

    public function test_agent_lihat_pembayaran(): void
    {
        $tx = $this->createSampleTransaction();

        $response = $this->actingAs($this->user)->get('/booking/' . $tx->id . '/payment');
        $response->assertStatus(200);
        $response->assertSee('Pembayaran');
    }

    public function test_agent_tidak_bisa_akses_pembayaran_orang_lain(): void
    {
        $tx = $this->createSampleTransaction();
        $otherUser = User::create([
            'name' => 'Orang Lain',
            'email' => 'other2@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)->get('/booking/' . $tx->id . '/payment');
        $response->assertStatus(403);
    }

    public function test_agent_cek_promo_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->get('/promo/check?' . http_build_query([
            'code' => 'GARUDA10',
        ]));
        $response->assertStatus(200);
        $response->assertJsonStructure(['valid', 'discount_type', 'discount', 'label']);
        $response->assertJson(['valid' => true]);
    }

    public function test_agent_cek_promo_invalid_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->get('/promo/check?' . http_build_query([
            'code' => 'KODESALAH',
        ]));
        $response->assertStatus(200);
        $response->assertJson(['valid' => false, 'message' => 'Kode promo tidak ditemukan.']);
    }

    public function test_agent_lihat_kursi_tersedia_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->get('/ajax/seats?' . http_build_query([
            'flight_id' => $this->flight->id,
            'class_type' => 'economy',
        ]));
        $response->assertStatus(200);
        $response->assertJsonStructure(['seats']);
    }

    public function test_agent_lihat_status_pembayaran_via_ajax(): void
    {
        $tx = $this->createSampleTransaction();

        $response = $this->actingAs($this->user)->get('/ajax/payment-status/' . $tx->id);
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'redirect_url']);
    }

    public function test_agent_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_diarahkan_ke_login_saat_booking(): void
    {
        $response = $this->get('/booking/create');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $response = $this->get('/booking/check');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_halaman_register_renders(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Buat Akun Baru');
    }

    public function test_halaman_login_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk');
    }

    public function test_agent_all_bookings_pages_exist(): void
    {
        $tx = $this->createSampleTransaction();
        $this->actingAs($this->user);
        $this->get('/booking/' . $tx->id)->assertStatus(200);
        $this->get('/my-bookings')->assertStatus(200);
        $this->get('/booking/check')->assertStatus(200);
    }

    public function test_agent_max_booking_flow(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'Max User',
            'email' => 'max@user.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);
        $response->assertRedirect('/dashboard');
    }

    public function test_agent_payment_flow_after_booking(): void
    {
        $tx = $this->createSampleTransaction();
        $this->actingAs($this->user);

        $this->get('/booking/' . $tx->id . '/payment')->assertStatus(200);

        $this->get('/booking/' . $tx->id)->assertStatus(200)->assertSee($tx->code);
    }

    public function test_agent_authentication_middleware_chain(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/my-bookings')->assertRedirect('/login');
        $this->get('/booking/create')->assertRedirect('/login');
        $this->get('/booking/check')->assertRedirect('/login');

        $this->actingAs($this->user);
        $this->get('/dashboard')->assertStatus(200);
        $this->get('/my-bookings')->assertStatus(200);
        $this->get('/booking/check')->assertStatus(200);
    }

    private function createSampleTransaction(): Transaction
    {
        return Transaction::create([
            'user_id' => $this->user->id,
            'code' => 'GRD-AGENT-' . strtoupper(substr(uniqid(), -6)),
            'flight_id' => $this->flight->id,
            'flight_class_id' => $this->economyClass->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => '08123456789',
            'number_of_passengers' => 1,
            'payment_status' => 'pending',
            'subtotal' => 1250000,
            'grandtotal' => 1387500,
        ]);
    }
}
