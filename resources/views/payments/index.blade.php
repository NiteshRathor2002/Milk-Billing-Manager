@extends('layouts.app')

@section('title', 'Payments Ledger')
@section('header_title', 'Payment Receipts & Ledger')

@section('content')
<div class="space-y-6" x-data="{ paymentModalOpen: false }">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('payments.index') }}" class="flex items-center space-x-3 w-full md:w-auto">
            <select name="customer_id" onchange="this.form.submit()" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500 w-full md:w-64">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->customer_code }})
                    </option>
                @endforeach
            </select>
            @if(request('customer_id'))
                <a href="{{ route('payments.index') }}" class="text-xs text-slate-500 hover:text-slate-900 font-semibold">Reset</a>
            @endif
        </form>

        <button @click="paymentModalOpen = true" class="w-full md:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-xs transition-colors flex items-center justify-center space-x-2">
            <span>+ Record New Payment</span>
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Payment Date</th>
                        <th class="py-4 px-6">Customer</th>
                        <th class="py-4 px-6">Amount Paid</th>
                        <th class="py-4 px-6">Payment Method</th>
                        <th class="py-4 px-6">Transaction Ref / Notes</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6 text-slate-800 font-semibold">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('customers.show', $payment->customer->id) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                    {{ $payment->customer->name }}
                                </a>
                                <div class="text-xs text-slate-400 font-mono">{{ $payment->customer->customer_code }}</div>
                            </td>
                            <td class="py-4 px-6 font-extrabold text-emerald-600">
                                ₹{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                                    {{ $payment->payment_method === 'upi' ? 'bg-sky-100 text-sky-800' : '' }}
                                    {{ $payment->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $payment->payment_method === 'bank_transfer' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ $payment->payment_method === 'cheque' ? 'bg-amber-100 text-amber-800' : '' }}
                                ">
                                    {{ $payment->payment_method }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-600">
                                {{ $payment->transaction_ref ?: $payment->notes ?: '-' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Delete this payment entry?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-500 hover:underline font-semibold">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No payments recorded matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <div x-show="paymentModalOpen" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100" @click.away="paymentModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h4 class="text-base font-bold text-slate-900">Record Received Payment</h4>
                <button @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('payments.store') }}" method="POST" class="space-y-4 pt-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Customer *</label>
                    <select name="customer_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->customer_code }}) - Bal: ₹{{ number_format($c->effective_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount Paid (₹) *</label>
                    <input type="number" step="any" min="1" name="amount" required placeholder="e.g. 1200"
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
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Transaction Ref / Note</label>
                    <input type="text" name="notes" placeholder="e.g. Received via GPay from Rajesh"
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
