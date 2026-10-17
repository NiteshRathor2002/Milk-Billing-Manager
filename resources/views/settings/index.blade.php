@extends('layouts.app')

@section('title', 'Application Settings')
@section('header_title', 'Business Profile & Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <h3 class="text-lg font-bold text-slate-900">Dairy Information & Invoice Customization</h3>
            <p class="text-xs text-slate-500 mt-1">These details appear on generated PDF invoices and customer WhatsApp communications.</p>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Business / Dairy Name *</label>
                    <input type="text" name="business_name" value="{{ old('business_name', $settings->business_name) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Business Phone / WhatsApp *</label>
                    <input type="text" name="business_phone" value="{{ old('business_phone', $settings->business_phone) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Business Email</label>
                    <input type="email" name="business_email" value="{{ old('business_email', $settings->business_email) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">UPI ID for Customer Payments</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id', $settings->upi_id) }}" placeholder="e.g. dairyfarm@upi"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                    <p class="text-xs text-slate-400 mt-1">Appears on invoices and WhatsApp payment reminders.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Title *</label>
                    <input type="text" name="bill_title" value="{{ old('bill_title', $settings->bill_title) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Currency Symbol *</label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Business / Dairy Address</label>
                <textarea name="business_address" rows="2"
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">{{ old('business_address', $settings->business_address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Footer Note / Terms</label>
                <textarea name="bill_footer" rows="2"
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">{{ old('bill_footer', $settings->bill_footer) }}</textarea>
            </div>

            <div class="flex justify-end pt-3">
                <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-xs transition-colors">
                    Save Business Details
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <h3 class="text-lg font-bold text-slate-900">Security & Admin Password</h3>
            <p class="text-xs text-slate-500 mt-1">Change your administrator login password.</p>
        </div>

        <form action="{{ route('settings.password') }}" method="POST" class="space-y-5 max-w-lg">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Current Password *</label>
                <input type="password" name="current_password" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password *</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm New Password *</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex justify-end pt-3">
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-xs transition-colors">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
