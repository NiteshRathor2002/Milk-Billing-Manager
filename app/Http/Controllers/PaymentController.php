<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Bill;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'bill'])->orderBy('payment_date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $payments = $query->paginate(20)->withQueryString();
        $customers = Customer::orderBy('name')->get();
        $settings = AppSetting::getSettings();

        return view('payments.index', compact('payments', 'customers', 'settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bill_id' => 'nullable|exists:bills,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'transaction_ref' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        Payment::create($validated);

        return back()->with('success', 'Payment of ' . ($validated['amount']) . ' recorded successfully.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return back()->with('success', 'Payment record deleted.');
    }
}
