@extends('layouts.app')

@section('title', $customer->name . ' - Customer Details')
@section('header_title', 'Customer 360 Overview')

@section('content')
<div class="space-y-8" x-data="{ paymentModalOpen: false }">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start md:items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-extrabold flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/20 shrink-0">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-2xl font-extrabold text-slate-900">{{ $customer->name }}</h2>
                    @if($customer->status === 'active')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                            Inactive
                        </span>
                    @endif
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                    <span class="font-mono bg-slate-100 px-2 py-0.5 rounded-md font-semibold text-slate-700">{{ $customer->customer_code }}</span>
                    <span>•</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->whatsapp_number) }}" target="_blank" class="text-emerald-600 hover:underline inline-flex items-center space-x-1 font-semibold">
                        <span>💬 {{ $customer->whatsapp_number }}</span>
                    </a>
                    <span>•</span>
                    <span>Joined {{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('calendar.show', $customer->id) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-xs transition-colors inline-flex items-center space-x-2">
                <span>📅 Delivery Calendar</span>
            </a>
            <a href="{{ route('billing.preview', ['customerId' => $customer->id, 'year' => $now->year, 'month' => $now->month]) }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-sm transition-colors inline-flex items-center space-x-1">
                <span>🧾 Current Bill</span>
            </a>
            <button @click="paymentModalOpen = true" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl text-sm transition-colors">
                + Record Payment
            </button>
            <a href="{{ route('customers.edit', $customer->id) }}" class="p-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors" title="Edit Customer">
                ✏️
            </a>
            <form action="{{ route('customers.toggle-status', $customer->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="p-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors" title="Toggle Status">
                    🔄
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Default Configuration</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Daily Milk Volume:</span>
                    <span class="font-bold text-slate-900">{{ $customer->daily_quantity }} Litres</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Rate per Litre:</span>
                    <span class="font-bold text-slate-900">₹{{ number_format($customer->rate_per_litre, 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Standard Daily Cost:</span>
                    <span class="font-bold text-emerald-600">₹{{ number_format($customer->daily_quantity * $customer->rate_per_litre, 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Carried Balance:</span>
                    <span class="font-bold text-slate-900">₹{{ number_format($customer->previous_balance, 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 text-xs block mb-1">Address:</span>
                    <p class="text-slate-700 text-xs bg-slate-50 p-2.5 rounded-lg">{{ $customer->address ?: 'No delivery address recorded' }}</p>
                </div>
                @if($customer->notes)
                    <div>
                        <span class="text-slate-500 text-xs block mb-1">Delivery Instructions:</span>
                        <p class="text-slate-700 text-xs bg-slate-50 p-2.5 rounded-lg italic">{{ $customer->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white shadow-lg space-y-4 md:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Live Month Projection</span>
                    <h3 class="text-2xl font-extrabold text-white mt-1">{{ $now->format('F Y') }}</h3>
                </div>
                <div class="text-right">
                    <span class="text-xs text-emerald-100 block">Total Outstanding Balance</span>
                    <span class="text-2xl font-extrabold text-white">₹{{ number_format($customer->effective_balance, 2) }}</span>
                </div>
            </div>

            @if($currentMonthCalc['applicable'])
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-emerald-400/40 text-center">
                    <div class="bg-white/10 rounded-2xl p-3 backdrop-blur-xs">
                        <span class="text-xs text-emerald-100 block">Total Litres</span>
                        <span class="text-lg font-extrabold text-white">{{ $currentMonthCalc['total_milk_quantity'] }} L</span>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-3 backdrop-blur-xs">
                        <span class="text-xs text-emerald-100 block">Milk Charges</span>
                        <span class="text-lg font-extrabold text-white">₹{{ number_format($currentMonthCalc['milk_charges'], 2) }}</span>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-3 backdrop-blur-xs">
                        <span class="text-xs text-emerald-100 block">Carried Prev</span>
                        <span class="text-lg font-extrabold text-white">₹{{ number_format($currentMonthCalc['previous_balance'], 2) }}</span>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-3 backdrop-blur-xs">
                        <span class="text-xs text-emerald-100 block">Estimated Due</span>
                        <span class="text-lg font-extrabold text-white">₹{{ number_format($currentMonthCalc['amount_due'], 2) }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between text-xs text-emerald-100 pt-2">
                    <div>
                        Delivery breakdown: 
                        <span class="font-bold text-white">{{ $currentMonthCalc['normal_days'] }}</span> Normal • 
                        <span class="font-bold text-white">{{ $currentMonthCalc['holiday_days'] }}</span> Holidays • 
                        <span class="font-bold text-white">{{ $currentMonthCalc['reduced_days'] }}</span> Reduced • 
                        <span class="font-bold text-white">{{ $currentMonthCalc['extra_days'] }}</span> Extra
                    </div>
                    <a href="{{ route('calendar.show', $customer->id) }}" class="underline font-bold text-white hover:text-emerald-200">
                        View Daily Calendar →
                    </a>
                </div>
            @else
                <div class="py-4 text-emerald-100 text-sm">
                    {{ $currentMonthCalc['reason'] }}
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">Issued Monthly Bills</h3>
            <a href="{{ route('billing.preview', ['customerId' => $customer->id, 'year' => $now->year, 'month' => $now->month]) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                Generate New Bill →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6">Bill Number</th>
                        <th class="py-3.5 px-6">Month</th>
                        <th class="py-3.5 px-6">Period</th>
                        <th class="py-3.5 px-6">Total Litres</th>
                        <th class="py-3.5 px-6">Milk Charges</th>
                        <th class="py-3.5 px-6">Amount Due</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->bills as $bill)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-6 font-mono font-bold text-slate-900">
                                <a href="{{ route('bills.show', $bill->id) }}" class="hover:text-emerald-600">
                                    {{ $bill->bill_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-6 font-semibold text-slate-700">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y') }}
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse($bill->period_start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($bill->period_end_date)->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-6 font-bold text-slate-800">
                                {{ $bill->total_milk_quantity }} L
                            </td>
                            <td class="py-3.5 px-6 text-slate-800">
                                ₹{{ number_format($bill->milk_charges, 2) }}
                            </td>
                            <td class="py-3.5 px-6 font-extrabold text-slate-900">
                                ₹{{ number_format($bill->amount_due, 2) }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center space-x-1.5">
                                    <a href="{{ route('bills.whatsapp', $bill->id) }}" target="_blank" title="WhatsApp Share" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                        💬
                                    </a>
                                    <a href="{{ route('bills.pdf.download', $bill->id) }}" title="Download PDF" class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                                        📥
                                    </a>
                                    <a href="{{ route('bills.show', $bill->id) }}" title="View Snapshot" class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                                        👁️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                No issued bills recorded for this customer yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">Payment History Ledger</h3>
            <button @click="paymentModalOpen = true" class="text-xs font-bold text-emerald-600 hover:underline">
                + Add Payment
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6">Payment Date</th>
                        <th class="py-3.5 px-6">Amount</th>
                        <th class="py-3.5 px-6">Method</th>
                        <th class="py-3.5 px-6">Reference / Notes</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->payments as $payment)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-6 text-slate-800">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-6 font-extrabold text-emerald-600">
                                ₹{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 uppercase">
                                    {{ $payment->payment_method }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-600">
                                {{ $payment->transaction_ref ?: $payment->notes ?: '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Delete this payment record?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-500 hover:underline font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                No payment receipts recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="paymentModalOpen" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100" @click.away="paymentModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h4 class="text-base font-bold text-slate-900">Record Payment for {{ $customer->name }}</h4>
                <button @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('payments.store') }}" method="POST" class="space-y-4 pt-4">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount Paid (₹) *</label>
                    <input type="number" step="any" min="1" name="amount" required placeholder="e.g. 1500"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm font-bold focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / GPay / PhonePe</option>
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Transaction Ref / Notes</label>
                    <input type="text" name="notes" placeholder="e.g. UPI Ref #12345678"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-2">
                    <button type="button" @click="paymentModalOpen = false" class="px-4 py-2 rounded-xl text-slate-600 text-sm font-semibold hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs">
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
