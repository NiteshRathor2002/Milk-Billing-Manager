@extends('layouts.app')

@section('title', 'Bill Preview - ' . $customer->name)
@section('header_title', 'Monthly Bill Preview & Review')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <a href="{{ route('calendar.show', ['customerId' => $customer->id, 'year' => $year, 'month' => $month]) }}" class="text-emerald-600 hover:underline">
                    ← Delivery Calendar
                </a>
                <span>•</span>
                <span>Billing Period: {{ $monthName }}</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900">{{ $customer->name }}</h2>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="font-mono">{{ $customer->customer_code }}</span>
                <span>•</span>
                <span>Rate: <strong>₹{{ number_format($customer->rate_per_litre, 2) }}/L</strong></span>
                <span>•</span>
                <span>Effective Start: <strong>{{ \Carbon\Carbon::parse($calc['period_start'])->format('d M Y') }}</strong></span>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            @if($existingBill)
                <div class="text-right mr-2">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Already Generated</span>
                    <div class="text-[11px] text-slate-400 font-mono">{{ $existingBill->bill_number }}</div>
                </div>
            @endif

            <form action="{{ route('billing.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-md transition-all flex items-center space-x-2">
                    <span>🧾 {{ $existingBill ? 'Update & Re-issue Bill' : 'Finalize & Issue Bill' }}</span>
                </button>
            </form>
        </div>
    </div>

    @if($calc['applicable'])
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Volume</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-2 block">{{ $calc['total_milk_quantity'] }} L</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Delivered this month</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Milk Charges</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-2 block">₹{{ number_format($calc['milk_charges'], 2) }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Daily sum of deliveries</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Previous Balance</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-2 block">₹{{ number_format($calc['previous_balance'], 2) }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Opening pending amount</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Payments / Credits</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-2 block">- ₹{{ number_format($calc['payments'], 2) }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Paid in this cycle</span>
            </div>

            <div class="bg-gradient-to-tr from-emerald-600 to-teal-600 p-5 rounded-2xl text-white shadow-md col-span-2 sm:col-span-1">
                <span class="text-xs font-bold text-emerald-100 uppercase tracking-wider block">Total Amount Due</span>
                <span class="text-2xl font-extrabold text-white mt-2 block">₹{{ number_format($calc['amount_due'], 2) }}</span>
                <span class="text-[11px] text-emerald-100 mt-1 block">Net payable invoice total</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Itemized Daily Deliveries Breakdown</h3>
                    <p class="text-xs text-slate-500">Every calendar day calculation adhering to registration dates and delivery exceptions.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-6">Date</th>
                            <th class="py-3.5 px-6">Delivery Type</th>
                            <th class="py-3.5 px-6">Quantity (L)</th>
                            <th class="py-3.5 px-6">Rate (₹)</th>
                            <th class="py-3.5 px-6">Daily Cost</th>
                            <th class="py-3.5 px-6">Notes / Exceptions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($calc['days'] as $day)
                            <tr class="hover:bg-slate-50/60 {{ $day['is_before_reg'] ? 'opacity-40 bg-slate-50' : '' }}">
                                <td class="py-3.5 px-6 font-semibold text-slate-800">
                                    {{ \Carbon\Carbon::parse($day['date'])->format('d M Y (D)') }}
                                </td>
                                <td class="py-3.5 px-6">
                                    @if($day['is_before_reg'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-600">
                                            Pre-Registration
                                        </span>
                                    @elseif($day['type'] === 'holiday')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                            Holiday / No Delivery
                                        </span>
                                    @elseif($day['type'] === 'reduced')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            Reduced Milk
                                        </span>
                                    @elseif($day['type'] === 'extra')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800">
                                            Extra Milk
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Normal Delivery
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 font-bold text-slate-900">
                                    {{ $day['actual_quantity'] }} L
                                </td>
                                <td class="py-3.5 px-6 text-slate-700">
                                    ₹{{ number_format($day['rate'], 2) }}
                                </td>
                                <td class="py-3.5 px-6 font-extrabold text-slate-900">
                                    ₹{{ number_format($day['charge'], 2) }}
                                </td>
                                <td class="py-3.5 px-6 text-xs text-slate-500 italic">
                                    {{ $day['note'] ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-amber-800 text-sm">
            {{ $calc['reason'] }}
        </div>
    @endif
</div>
@endsection
