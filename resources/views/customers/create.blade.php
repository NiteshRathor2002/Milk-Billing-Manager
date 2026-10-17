@extends('layouts.app')

@section('title', 'Register Customer')
@section('header_title', 'Register New Customer')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Customer Registration</h2>
            <p class="text-sm text-slate-500">Configure customer information, default daily milk volume, rate, and opening balance.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            ← Back to Directory
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Customer Code *</label>
                    <input type="text" name="customer_code" value="{{ old('customer_code', $suggestedCode) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm font-mono focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Customer Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ramesh Patel"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">WhatsApp Number *</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', '+91 ') }}" required placeholder="+91 9876543210"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                    <p class="text-xs text-slate-400 mt-1">Used for 1-click WhatsApp bill dispatch.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Registration Date *</label>
                    <input type="date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                    <p class="text-xs text-slate-400 mt-1">Billing applies strictly starting from this date.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Default Daily Milk Quantity (Litres) *</label>
                    <input type="number" step="any" min="0.01" name="daily_quantity" value="{{ old('daily_quantity', '1.00') }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rate per Litre (₹) *</label>
                    <input type="number" step="any" min="0.01" name="rate_per_litre" value="{{ old('rate_per_litre', '60.00') }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Previous Pending Balance (₹)</label>
                    <input type="number" step="any" min="0" name="previous_balance" value="{{ old('previous_balance', '0.00') }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                    <p class="text-xs text-slate-400 mt-1">Carried forward initial outstanding opening balance.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Delivery Address (Optional)</label>
                <textarea name="address" rows="2" placeholder="House/Flat number, building, street, landmark"
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">{{ old('address') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Delivery Instructions / Notes (Optional)</label>
                <textarea name="notes" rows="2" placeholder="e.g. Ring bell, delivery time 6:00 AM, cow milk"
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('customers.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition-all">
                    Register Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
