<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\DailyDelivery;
use App\Models\Payment;
use App\Services\BillingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_first_month_billing_from_registration_date(): void
    {
        $customer = Customer::create([
            'customer_code' => 'MBS-TEST1',
            'name' => 'Test Customer',
            'whatsapp_number' => '+91 9999900001',
            'daily_quantity' => 2.00,
            'rate_per_litre' => 60.00,
            'registration_date' => '2026-10-10',
            'previous_balance' => 0.00,
            'status' => 'active',
        ]);

        $service = new BillingCalculationService();
        $calc = $service->getMonthlyCalculation($customer, 2026, 10);

        $this->assertTrue($calc['applicable']);
        $this->assertEquals('2026-10-10', $calc['period_start']);
        $this->assertEquals('2026-10-31', $calc['period_end']);

        $beforeRegDays = array_filter($calc['days'], fn($d) => $d['is_before_reg']);
        $this->assertCount(9, $beforeRegDays);

        $activeDays = array_filter($calc['days'], fn($d) => !$d['is_before_reg']);
        $this->assertCount(22, $activeDays);

        $this->assertEquals(44.00, $calc['total_milk_quantity']);
        $this->assertEquals(2640.00, $calc['milk_charges']);
        $this->assertEquals(2640.00, $calc['amount_due']);
    }

    public function test_subsequent_full_month_billing(): void
    {
        $customer = Customer::create([
            'customer_code' => 'MBS-TEST2',
            'name' => 'Test Customer 2',
            'whatsapp_number' => '+91 9999900002',
            'daily_quantity' => 2.00,
            'rate_per_litre' => 60.00,
            'registration_date' => '2026-10-10',
            'previous_balance' => 0.00,
            'status' => 'active',
        ]);

        $service = new BillingCalculationService();
        $calc = $service->getMonthlyCalculation($customer, 2026, 11);

        $this->assertTrue($calc['applicable']);
        $this->assertEquals('2026-11-01', $calc['period_start']);
        $this->assertEquals('2026-11-30', $calc['period_end']);

        $beforeRegDays = array_filter($calc['days'], fn($d) => $d['is_before_reg']);
        $this->assertCount(0, $beforeRegDays);

        $activeDays = array_filter($calc['days'], fn($d) => !$d['is_before_reg']);
        $this->assertCount(30, $activeDays);

        $this->assertEquals(60.00, $calc['total_milk_quantity']);
        $this->assertEquals(3600.00, $calc['milk_charges']);
    }

    public function test_delivery_exceptions_and_payments(): void
    {
        $customer = Customer::create([
            'customer_code' => 'MBS-TEST3',
            'name' => 'Test Customer 3',
            'whatsapp_number' => '+91 9999900003',
            'daily_quantity' => 2.00,
            'rate_per_litre' => 60.00,
            'registration_date' => '2026-10-01',
            'previous_balance' => 500.00,
            'status' => 'active',
        ]);

        DailyDelivery::create([
            'customer_id' => $customer->id,
            'delivery_date' => '2026-10-05',
            'delivery_type' => 'holiday',
            'default_quantity' => 2.00,
            'actual_quantity' => 0.00,
            'extra_quantity' => 0.00,
            'rate_applied' => 60.00,
            'daily_charge' => 0.00,
        ]);

        DailyDelivery::create([
            'customer_id' => $customer->id,
            'delivery_date' => '2026-10-06',
            'delivery_type' => 'reduced',
            'default_quantity' => 2.00,
            'actual_quantity' => 1.00,
            'extra_quantity' => 0.00,
            'rate_applied' => 60.00,
            'daily_charge' => 60.00,
        ]);

        DailyDelivery::create([
            'customer_id' => $customer->id,
            'delivery_date' => '2026-10-07',
            'delivery_type' => 'extra',
            'default_quantity' => 2.00,
            'actual_quantity' => 3.00,
            'extra_quantity' => 1.00,
            'rate_applied' => 60.00,
            'daily_charge' => 180.00,
        ]);

        Payment::create([
            'customer_id' => $customer->id,
            'amount' => 1000.00,
            'payment_date' => '2026-10-15',
            'payment_method' => 'upi',
        ]);

        $service = new BillingCalculationService();
        $calc = $service->getMonthlyCalculation($customer, 2026, 10);

        $this->assertEquals(1, $calc['holiday_days']);
        $this->assertEquals(1, $calc['reduced_days']);
        $this->assertEquals(1, $calc['extra_days']);
        $this->assertEquals(28, $calc['normal_days']);

        $expectedLitres = (28 * 2.0) + (1 * 0.0) + (1 * 1.0) + (1 * 3.0);
        $this->assertEquals($expectedLitres, $calc['total_milk_quantity']);

        $expectedMilkCharges = (28 * 2.0 * 60) + 0 + (1.0 * 60) + (3.0 * 60);
        $this->assertEquals($expectedMilkCharges, $calc['milk_charges']);

        $expectedDue = $expectedMilkCharges + 500.00 - 1000.00;
        $this->assertEquals($expectedDue, $calc['amount_due']);
    }
}
