<?php

namespace Tests\Unit;

use App\Services\TaxService;
use Tests\TestCase;

class TaxServiceTest extends TestCase
{
    private TaxService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaxService();
    }

    public function test_rate_returns_default_from_config(): void
    {
        $rate = $this->service->rate();

        $this->assertEquals(0.11, $rate);
    }

    public function test_calculates_tax_correctly(): void
    {
        $tax = $this->service->calculate(100000);

        $this->assertEquals(11000, $tax);
    }

    public function test_calculates_tax_for_large_amount(): void
    {
        $tax = $this->service->calculate(5000000);

        $this->assertEquals(550000, $tax);
    }

    public function test_calculates_tax_rounds_correctly(): void
    {
        $tax = $this->service->calculate(9999);

        $this->assertEquals(1100, $tax);
    }

    public function test_grand_total_without_discount(): void
    {
        $result = $this->service->grandTotal(200000);

        $this->assertEquals(200000, $result['subtotal']);
        $this->assertEquals(22000, $result['tax']);
        $this->assertEquals(0, $result['discount']);
        $this->assertEquals(222000, $result['grandtotal']);
    }

    public function test_grand_total_with_discount(): void
    {
        $result = $this->service->grandTotal(500000, 50000);

        $this->assertEquals(500000, $result['subtotal']);
        $this->assertEquals(55000, $result['tax']);
        $this->assertEquals(50000, $result['discount']);
        $this->assertEquals(505000, $result['grandtotal']);
    }

    public function test_grand_total_never_below_zero(): void
    {
        $result = $this->service->grandTotal(10000, 99999999);

        $this->assertEquals(0, $result['grandtotal']);
    }
}
