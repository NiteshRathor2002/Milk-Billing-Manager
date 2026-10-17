@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('header_title', 'Business Overview')

@section('content')
<div class="space-y-8">
    <div class="bg-gradient-to-r from-emerald-800 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-3">
                    🥛 {{ $settings->business_name }}
                </span>
                <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Welcome, Admin</h2>
                <p class="text-slate-300 text-sm mt-1">
                    Monthly billing cycle in progress for <span class="font-semibold text-emerald-400">{{ $currentMonthName }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('customers.create') }}" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl text-sm transition-all shadow-md">
                    + Register Customer
                </a>
                <a href="{{ route('billing.index') }}" class="px-4 py-2.5 bg-slate-800/80 hover:bg-slate-700 text-white font-semibold rounded-xl text-sm border border-slate-700 transition-all">
                    Generate Monthly Bills
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Customers</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg">
                    👥
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $activeCustomers }}</span>
                <span class="text-xs text-slate-500">/ {{ $totalCustomers }} Total</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center space-x-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $inactiveCustomers }} currently inactive</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Estimated Volume</span>
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-lg">
                    🥛
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-1">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($totalEstimatedLitres, 1) }}</span>
                <span class="text-sm font-semibold text-slate-600">Litres</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Projected total milk delivery this month
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Estimated Charges</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg">
                    ₹
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-1">
                <span class="text-3xl font-extrabold text-slate-900">₹{{ number_format($totalEstimatedCharges, 2) }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Current month milk charges
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Outstanding</span>
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg">
                    ⏳
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-1">
                <span class="text-3xl font-extrabold {{ $totalOutstanding > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    ₹{{ number_format($totalOutstanding, 2) }}
                </span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Pending balance across all customer accounts
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-base font-bold text-slate-900">Recent Delivery Exceptions</h3>
                </div>
                <span class="text-xs text-slate-500">Holidays, Extra & Reduced</span>
            </div>

            @if($recentExceptions->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($recentExceptions as $entry)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <a href="{{ route('customers.show', $entry->customer->id) }}" class="text-sm font-bold text-slate-900 hover:text-emerald-600 transition-colors">
                                        {{ $entry->customer->name }}
                                    </a>
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($entry->delivery_date)->format('D, d M Y') }}
                                        @if($entry->note)
                                            • <span class="italic text-slate-400">"{{ $entry->note }}"</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 text-right">
                                @if($entry->delivery_type === 'holiday')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                        Holiday (0L)
                                    </span>
                                @elseif($entry->delivery_type === 'reduced')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        Reduced ({{ $entry->actual_quantity }}L)
                                    </span>
                                @elseif($entry->delivery_type === 'extra')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">
                                        Extra (+{{ $entry->extra_quantity }}L)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        Normal ({{ $entry->actual_quantity }}L)
                                    </span>
                                @endif
                                <a href="{{ route('calendar.show', $entry->customer_id) }}" class="text-xs text-emerald-600 hover:underline font-semibold">
                                    Calendar →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-sm">
                    No exceptions logged yet. Ordinary days inherit default customer quantities.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-base font-bold text-slate-900">Recent Generated Bills</h3>
                </div>
                <a href="{{ route('billing.index') }}" class="text-xs text-indigo-600 hover:underline font-semibold">View All →</a>
            </div>

            @if($recentBills->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($recentBills as $bill)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-bold text-slate-900">
                                    {{ $bill->customer->name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $bill->bill_number }} • Month: {{ $bill->billing_month }}
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-extrabold text-slate-900">
                                    ₹{{ number_format($bill->amount_due, 2) }}
                                </span>
                                <a href="{{ route('bills.whatsapp', $bill->id) }}" target="_blank" title="Share on WhatsApp" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                    💬
                                </a>
                                <a href="{{ route('bills.pdf.download', $bill->id) }}" title="Download PDF" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                                    📥
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-sm">
                    No bills generated yet for this month. 
                    <a href="{{ route('billing.index') }}" class="text-emerald-600 font-semibold hover:underline">Click here to generate bills</a>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
