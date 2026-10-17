<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\AppSetting;
use App\Services\BillingCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_number', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $settings = AppSetting::getSettings();

        return view('customers.index', compact('customers', 'settings'));
    }

    public function create()
    {
        $lastCustomer = Customer::withTrashed()->orderBy('id', 'desc')->first();
        $nextNum = $lastCustomer ? ($lastCustomer->id + 1) : 1;
        $suggestedCode = sprintf('MBS-%03d', $nextNum);
        $settings = AppSetting::getSettings();

        return view('customers.create', compact('suggestedCode', 'settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:30|unique:customers,customer_code',
            'name' => 'required|string|max:150',
            'whatsapp_number' => 'required|string|max:20',
            'daily_quantity' => 'required|numeric|min:0.1',
            'rate_per_litre' => 'required|numeric|min:1',
            'registration_date' => 'required|date',
            'previous_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['previous_balance'] = $validated['previous_balance'] ?? 0.00;

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', "Customer {$customer->name} registered successfully!");
    }

    public function show($id, BillingCalculationService $billingService)
    {
        $customer = Customer::with(['bills' => function ($q) {
            $q->orderBy('billing_month', 'desc');
        }, 'payments' => function ($q) {
            $q->orderBy('payment_date', 'desc');
        }])->findOrFail($id);

        $now = Carbon::now();
        $currentMonthCalc = $billingService->getMonthlyCalculation($customer, $now->year, $now->month);
        $settings = AppSetting::getSettings();

        return view('customers.show', compact('customer', 'currentMonthCalc', 'settings', 'now'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $settings = AppSetting::getSettings();
        return view('customers.edit', compact('customer', 'settings'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'customer_code' => "required|string|max:30|unique:customers,customer_code,{$id}",
            'name' => 'required|string|max:150',
            'whatsapp_number' => 'required|string|max:20',
            'daily_quantity' => 'required|numeric|min:0.1',
            'rate_per_litre' => 'required|numeric|min:1',
            'registration_date' => 'required|date',
            'previous_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['previous_balance'] = $validated['previous_balance'] ?? 0.00;

        $customer->update($validated);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', "Customer {$customer->name} updated successfully.");
    }

    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = ($customer->status === 'active') ? 'inactive' : 'active';
        $customer->save();

        $statusLabel = ucfirst($customer->status);
        return back()->with('success', "Customer {$customer->name} is now {$statusLabel}.");
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        if ($customer->bills()->count() > 0) {
            $customer->status = 'inactive';
            $customer->save();
            return back()->with('warning', "Customer has issued bills, so status was set to Inactive to preserve financial history.");
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
