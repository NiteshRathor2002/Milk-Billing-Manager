<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/customers');
        $response->assertRedirect('/login');

        $response = $this->get('/billing');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Welcome, Admin');
    }

    public function test_admin_can_create_and_manage_customer(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->actingAs($user);

        $response = $this->post('/customers', [
            'customer_code' => 'MBS-100',
            'name' => 'Amit Verma',
            'whatsapp_number' => '+91 9876543210',
            'daily_quantity' => 2.50,
            'rate_per_litre' => 65.00,
            'registration_date' => '2026-10-01',
            'previous_balance' => 150.00,
            'address' => 'Sector 15, Block B',
            'status' => 'active',
        ]);

        $customer = Customer::where('customer_code', 'MBS-100')->first();
        $this->assertNotNull($customer);
        $response->assertRedirect(route('customers.show', $customer->id));

        $showResponse = $this->get(route('customers.show', $customer->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Amit Verma');
    }

    public function test_jwt_api_authentication(): void
    {
        $unauthResponse = $this->getJson('/api/me');
        $unauthResponse->assertStatus(401);

        $user = User::create([
            'name' => 'Admin API',
            'email' => 'api@test.com',
            'password' => Hash::make('api12345'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api@test.com',
            'password' => 'api12345',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'token', 'user']);

        $token = $response->json('token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/me');
        $meResponse->assertStatus(200);
        $meResponse->assertJson(['success' => true]);
    }

    public function test_bill_generation_and_pdf_download(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->actingAs($user);

        $customer = Customer::create([
            'customer_code' => 'MBS-200',
            'name' => 'Kavita Singh',
            'whatsapp_number' => '+91 9876500200',
            'daily_quantity' => 1.50,
            'rate_per_litre' => 60.00,
            'registration_date' => '2026-10-01',
            'previous_balance' => 0.00,
            'status' => 'active',
        ]);

        $genResponse = $this->post('/billing/generate', [
            'customer_id' => $customer->id,
            'year' => 2026,
            'month' => 10,
        ]);

        $bill = Bill::where('customer_id', $customer->id)->where('billing_month', '2026-10')->first();
        $this->assertNotNull($bill);
        $genResponse->assertRedirect(route('bills.show', $bill->id));

        $pdfResponse = $this->get(route('bills.pdf.download', $bill->id));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');

        $waResponse = $this->get(route('bills.whatsapp', $bill->id));
        $waResponse->assertStatus(302);
        $this->assertStringContainsString('wa.me', $waResponse->headers->get('Location'));
    }

    public function test_payment_recording_and_settings_update(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->actingAs($user);

        $customer = Customer::create([
            'customer_code' => 'MBS-300',
            'name' => 'Deepak Rawat',
            'whatsapp_number' => '+91 9876500300',
            'daily_quantity' => 2.00,
            'rate_per_litre' => 60.00,
            'registration_date' => '2026-10-01',
            'previous_balance' => 800.00,
            'status' => 'active',
        ]);

        $payResponse = $this->post('/payments', [
            'customer_id' => $customer->id,
            'amount' => 500.00,
            'payment_date' => '2026-10-10',
            'payment_method' => 'cash',
            'notes' => 'Advance cash payment',
        ]);

        $payResponse->assertStatus(302);
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'amount' => 500.00,
        ]);

        $setResponse = $this->post('/settings', [
            'business_name' => 'Super Fresh Dairy',
            'business_phone' => '+91 9998887776',
            'business_email' => 'contact@superfresh.com',
            'business_address' => 'Dairy Farm Road',
            'bill_title' => 'Fresh Milk Delivery Invoice',
            'bill_footer' => 'Pay by 10th',
            'currency_symbol' => '₹',
        ]);

        $setResponse->assertStatus(302);
        $this->assertDatabaseHas('app_settings', [
            'business_name' => 'Super Fresh Dairy',
        ]);
    }
}
