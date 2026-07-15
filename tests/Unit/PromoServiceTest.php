<?php

namespace Tests\Unit;

use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Transaction;
use App\Models\User;
use App\Exceptions\PromoValidationException;
use App\Services\PromoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoServiceTest extends TestCase
{
    use RefreshDatabase;

    private PromoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PromoService();
    }

    public function test_validates_existing_promo_code(): void
    {
        $promo = PromoCode::create([
            'code' => 'DISKON50',
            'discount_type' => 'percentage',
            'discount' => 50,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $result = $this->service->validate('diskon50');

        $this->assertNotNull($result);
        $this->assertEquals($promo->id, $result->id);
    }

    public function test_returns_null_for_invalid_code(): void
    {
        $result = $this->service->validate('TIDAKADA');

        $this->assertNull($result);
    }

    public function test_returns_null_for_expired_code(): void
    {
        PromoCode::create([
            'code' => 'EXPIRED',
            'discount_type' => 'percentage',
            'discount' => 10,
            'valid_until' => Carbon::now()->subDay(),
            'is_active' => true,
        ]);

        $result = $this->service->validate('EXPIRED');

        $this->assertNull($result);
    }

    public function test_returns_valid_promo_even_if_used(): void
    {
        PromoCode::create([
            'code' => 'USED',
            'discount_type' => 'percentage',
            'discount' => 10,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $result = $this->service->validate('USED');

        $this->assertNotNull($result);
        $this->assertEquals('USED', $result->code);
    }

    public function test_calculates_percentage_discount(): void
    {
        $promo = PromoCode::create([
            'code' => 'HEM10',
            'discount_type' => 'percentage',
            'discount' => 10,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $discount = $this->service->calculateDiscount($promo, 100000, 11000);

        $this->assertEquals(11100, $discount);
    }

    public function test_calculates_fixed_discount(): void
    {
        $promo = PromoCode::create([
            'code' => 'POT50000',
            'discount_type' => 'fixed',
            'discount' => 50000,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $discount = $this->service->calculateDiscount($promo, 200000, 22000);

        $this->assertEquals(50000, $discount);
    }

    public function test_discount_does_not_exceed_total(): void
    {
        $promo = PromoCode::create([
            'code' => 'GRATIS',
            'discount_type' => 'fixed',
            'discount' => 99999999,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $discount = $this->service->calculateDiscount($promo, 50000, 5500);

        $this->assertEquals(55500, $discount);
    }

    public function test_apply_returns_valid_result(): void
    {
        PromoCode::create([
            'code' => 'DISC10',
            'discount_type' => 'percentage',
            'discount' => 10,
            'valid_until' => Carbon::now()->addDays(1),
            'is_active' => true,
        ]);

        $result = $this->service->apply('DISC10', 200000, 22000);

        $this->assertTrue($result['valid']);
        $this->assertGreaterThan(0, $result['discount']);
        $this->assertEquals('DISC10', $result['code']);
    }

    public function test_apply_returns_invalid_for_wrong_code(): void
    {
        $result = $this->service->apply('SALAH', 100000, 11000);

        $this->assertFalse($result['valid']);
        $this->assertEquals(0, $result['discount']);
    }

    public function test_successful_payment_records_usage_and_increments_counter_once(): void
    {
        $user = User::factory()->create();
        $promo = PromoCode::create([
            'code' => 'LIMIT1',
            'discount_type' => 'fixed',
            'discount' => 10000,
            'valid_until' => now()->addDay(),
            'usage_limit' => 1,
        ]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'promo_code_id' => $promo->id,
            'payment_status' => 'pending',
        ]);

        $this->service->completePayment($transaction, 'qris');
        $this->service->completePayment($transaction, 'qris');

        $this->assertDatabaseHas('promo_code_usages', ['promo_code_id' => $promo->id, 'user_id' => $user->id, 'transaction_id' => $transaction->id]);
        $this->assertSame(1, PromoCodeUsage::count());
        $this->assertSame(1, $promo->fresh()->used_count);
        $this->assertSame('paid', $transaction->fresh()->payment_status);
    }

    public function test_payment_rejects_a_promo_that_has_reached_its_limit(): void
    {
        $promo = PromoCode::create([
            'code' => 'HABIS',
            'discount_type' => 'fixed',
            'discount' => 10000,
            'valid_until' => now()->addDay(),
            'usage_limit' => 1,
            'used_count' => 1,
        ]);
        $transaction = Transaction::factory()->create(['promo_code_id' => $promo->id, 'payment_status' => 'pending']);

        $this->expectException(PromoValidationException::class);
        $this->expectExceptionMessage('Kode promo telah mencapai batas maksimum penggunaan.');

        $this->service->completePayment($transaction);
    }
}
