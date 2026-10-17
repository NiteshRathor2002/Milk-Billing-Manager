@extends('layouts.app')

@section('title', 'Customers Directory')
@section('header_title', 'Customer Directory')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, code..." 
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="status" onchange="this.form.submit()" class="w-full sm:w-40 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">Clear</a>
            @endif
        </form>

        <a href="{{ route('customers.create') }}" class="w-full md:w-auto inline-flex items-center justify-center space-x-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition-colors text-sm">
            <span>+ Add New Customer</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Customer</th>
                        <th class="py-4 px-6">Daily Qty</th>
                        <th class="py-4 px-6">Rate / Litre</th>
                        <th class="py-4 px-6">Registered Date</th>
                        <th class="py-4 px-6">Outstanding</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-50 to-teal-100 text-emerald-700 font-bold flex items-center justify-center text-sm shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('customers.show', $customer->id) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors">
                                            {{ $customer->name }}
                                        </a>
                                        <div class="flex items-center space-x-2 text-xs text-slate-500">
                                            <span class="font-mono">{{ $customer->customer_code }}</span>
                                            <span>•</span>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->whatsapp_number) }}" target="_blank" class="text-emerald-600 hover:underline inline-flex items-center space-x-0.5">
                                                <span>{{ $customer->whatsapp_number }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900">{{ $customer->daily_quantity }}</span>
                                <span class="text-xs text-slate-500">L / day</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900">₹{{ number_format($customer->rate_per_litre, 2) }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold {{ $customer->effective_balance > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                                    ₹{{ number_format($customer->effective_balance, 2) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($customer->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center space-x-1">
                                    <a href="{{ route('calendar.show', $customer->id) }}" title="Delivery Calendar" class="p-2 rounded-lg text-emerald-700 hover:bg-emerald-50 transition-colors">
                                        📅
                                    </a>
                                    <a href="{{ route('customers.show', $customer->id) }}" title="View Profile" class="p-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors">
                                        👁️
                                    </a>
                                    <a href="{{ route('customers.edit', $customer->id) }}" title="Edit Customer" class="p-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors">
                                        ✏️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No customers found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
