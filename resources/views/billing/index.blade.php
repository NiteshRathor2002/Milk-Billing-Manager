@extends('layouts.app')

@section('title', 'Monthly Billing Management')
@section('header_title', 'Monthly Invoicing & Billing')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'issued' }">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('billing.index') }}" class="flex items-center space-x-3 w-full md:w-auto">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Billing Month:</label>
            <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                   class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500">
            <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800">
                Go
            </button>
        </form>

        <div class="flex items-center space-x-2 bg-slate-100 p-1 rounded-2xl w-full md:w-auto">
            <button @click="activeTab = 'issued'" :class="activeTab === 'issued' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 font-medium'" class="flex-1 md:flex-initial px-4 py-2 rounded-xl text-xs transition-all">
                Generated Bills ({{ $bills->total() }})
            </button>
            <button @click="activeTab = 'unbilled'" :class="activeTab === 'unbilled' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 font-medium'" class="flex-1 md:flex-initial px-4 py-2 rounded-xl text-xs transition-all">
                Unbilled Customers ({{ $unbilledCustomers->count() }})
            </button>
        </div>
    </div>

    <div x-show="activeTab === 'issued'" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Bill Number</th>
                        <th class="py-4 px-6">Customer</th>
                        <th class="py-4 px-6">Total Milk</th>
                        <th class="py-4 px-6">Milk Charges</th>
                        <th class="py-4 px-6">Previous Balance</th>
                        <th class="py-4 px-6">Amount Due</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                <a href="{{ route('bills.show', $bill->id) }}" class="hover:text-emerald-600">
                                    {{ $bill->bill_number }}
                                </a>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $bill->customer->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $bill->customer->customer_code }}</div>
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                {{ $bill->total_milk_quantity }} Litres
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-800">
                                ₹{{ number_format($bill->milk_charges, 2) }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                ₹{{ number_format($bill->previous_balance, 2) }}
                            </td>
                            <td class="py-4 px-6 font-extrabold text-slate-900">
                                ₹{{ number_format($bill->amount_due, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center space-x-1.5">
                                    <a href="{{ route('bills.whatsapp', $bill->id) }}" target="_blank" title="1-Click WhatsApp Share" class="p-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors">
                                        💬
                                    </a>
                                    <a href="{{ route('bills.pdf.download', $bill->id) }}" title="Download PDF" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                        📥
                                    </a>
                                    <a href="{{ route('bills.show', $bill->id) }}" title="View Snapshot" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                        👁️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No bills issued for month {{ $selectedMonth }} yet. Switch to "Unbilled Customers" to preview and generate bills.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bills->links() }}
            </div>
        @endif
    </div>

    <div x-show="activeTab === 'unbilled'" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Customer Code</th>
                        <th class="py-4 px-6">Customer Name</th>
                        <th class="py-4 px-6">Default Daily Qty</th>
                        <th class="py-4 px-6">Rate / Litre</th>
                        <th class="py-4 px-6">Registration Date</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($unbilledCustomers as $customer)
                        @php
                            $dateObj = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-700">
                                {{ $customer->customer_code }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900">
                                {{ $customer->name }}
                            </td>
                            <td class="py-4 px-6">
                                {{ $customer->daily_quantity }} L
                            </td>
                            <td class="py-4 px-6 font-medium">
                                ₹{{ number_format($customer->rate_per_litre, 2) }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs">
                                {{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('billing.preview', ['customerId' => $customer->id, 'year' => $dateObj->year, 'month' => $dateObj->month]) }}" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                                    <span>Preview & Finalize Bill →</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                All active customers have been billed for {{ $selectedMonth }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
