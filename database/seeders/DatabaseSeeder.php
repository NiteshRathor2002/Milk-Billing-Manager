<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\DailyDelivery;
use App\Models\AppSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@milkmanager.com'],
            [
                'name' => 'Dairy Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        AppSetting::updateOrCreate(
            ['id' => 1],
            [
                'business_name' => 'Shree Krishna Dairy Farm',
                'business_phone' => '+91 9876543210',
                'business_email' => 'admin@milkmanager.com',
                'business_address' => 'Plot 45, Dairy Enclave, Sector 12, India',
                'upi_id' => 'dairyfarm@upi',
                'bill_title' => 'Fresh Milk Delivery Invoice',
                'bill_footer' => 'Pure and Fresh Cow & Buffalo Milk delivered daily. Please clear dues by the 10th of each month.',
                'currency_symbol' => '₹',
            ]
        );

        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        $c1 = Customer::updateOrCreate(
            ['customer_code' => 'MBS-001'],
            [
                'name' => 'Rajesh Sharma',
                'whatsapp_number' => '+91 9876500001',
                'daily_quantity' => 2.00,
                'rate_per_litre' => 65.00,
                'registration_date' => Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d'),
                'previous_balance' => 350.00,
                'address' => 'Flat 204, Royal Palms, Sector 4',
                'notes' => 'Morning delivery between 6:00 AM - 6:30 AM',
                'status' => 'active',
            ]
        );

        $c2 = Customer::updateOrCreate(
            ['customer_code' => 'MBS-002'],
            [
                'name' => 'Priya Patel',
                'whatsapp_number' => '+91 9876500002',
                'daily_quantity' => 1.50,
                'rate_per_litre' => 60.00,
                'registration_date' => Carbon::createFromDate($currentYear, $currentMonth, 10)->format('Y-m-d'),
                'previous_balance' => 0.00,
                'address' => 'House 12-B, Green Avenue',
                'notes' => 'Ring bell twice and leave bottle in basket',
                'status' => 'active',
            ]
        );

        $c3 = Customer::updateOrCreate(
            ['customer_code' => 'MBS-003'],
            [
                'name' => 'Vikram Singh',
                'whatsapp_number' => '+91 9876500003',
                'daily_quantity' => 3.00,
                'rate_per_litre' => 65.00,
                'registration_date' => Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d'),
                'previous_balance' => 500.00,
                'address' => 'Villa 7, Palm Meadows',
                'notes' => 'Full cream buffalo milk preferred',
                'status' => 'active',
            ]
        );

        $c4 = Customer::updateOrCreate(
            ['customer_code' => 'MBS-004'],
            [
                'name' => 'Sunita Verma',
                'whatsapp_number' => '+91 9876500004',
                'daily_quantity' => 1.00,
                'rate_per_litre' => 60.00,
                'registration_date' => Carbon::createFromDate($currentYear, $currentMonth, 1)->subMonth()->format('Y-m-d'),
                'previous_balance' => 120.00,
                'address' => 'Apt 101, Krishna Heights',
                'notes' => 'Customer temporarily on holiday',
                'status' => 'inactive',
            ]
        );

        DailyDelivery::updateOrCreate(
            ['customer_id' => $c1->id, 'delivery_date' => Carbon::createFromDate($currentYear, $currentMonth, 3)->format('Y-m-d')],
            [
                'delivery_type' => 'holiday',
                'default_quantity' => 2.00,
                'actual_quantity' => 0.00,
                'extra_quantity' => 0.00,
                'rate_applied' => 65.00,
                'daily_charge' => 0.00,
                'note' => 'Family out of town',
            ]
        );

        DailyDelivery::updateOrCreate(
            ['customer_id' => $c1->id, 'delivery_date' => Carbon::createFromDate($currentYear, $currentMonth, 7)->format('Y-m-d')],
            [
                'delivery_type' => 'extra',
                'default_quantity' => 2.00,
                'actual_quantity' => 3.00,
                'extra_quantity' => 1.00,
                'rate_applied' => 65.00,
                'daily_charge' => 195.00,
                'note' => 'Guests visiting',
            ]
        );

        DailyDelivery::updateOrCreate(
            ['customer_id' => $c1->id, 'delivery_date' => Carbon::createFromDate($currentYear, $currentMonth, 12)->format('Y-m-d')],
            [
                'delivery_type' => 'reduced',
                'default_quantity' => 2.00,
                'actual_quantity' => 1.00,
                'extra_quantity' => 0.00,
                'rate_applied' => 65.00,
                'daily_charge' => 65.00,
                'note' => 'Asked for 1 Litre only',
            ]
        );
    }
}
