@extends('layouts.app')

@section('title', 'Bill ' . $bill->bill_number)
@section('header_title', 'Issued Bill Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <a href="{{ route('billing.index', ['month' => $bill->billing_month]) }}" class="text-emerald-600 hover:underline">
                    ← Billing Directory
                </a>
                <span>•</span>
                <span class="font-mono text-slate-700">{{ $bill->bill_number }}</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900">{{ $customer->name }}</h2>
            <div class="text-xs text-slate-500 mt-1">
                Month: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y') }}</strong>
                • Generated: {{ \Carbon\Carbon::parse($bill->generated_at)->format('d M Y, h:i A') }}
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="handleWhatsAppShare()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition-colors inline-flex items-center space-x-1.5 cursor-pointer">
                <span>💬 Share on WhatsApp</span>
            </button>
            <a href="{{ route('bills.pdf.download', $bill->id) }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors inline-flex items-center space-x-1">
                <span>📥 Download PDF</span>
            </a>
            <a href="{{ route('bills.pdf.stream', $bill->id) }}" target="_blank" class="p-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors" title="Print / Stream PDF">
                🖨️
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-6 gap-4">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Supplier</span>
                <h3 class="text-lg font-extrabold text-slate-900">{{ $settings->business_name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $settings->business_phone }} • {{ $settings->business_email }}</p>
                <p class="text-xs text-slate-500">{{ $settings->business_address }}</p>
            </div>
            <div class="text-left md:text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Billed To</span>
                <h3 class="text-base font-extrabold text-slate-900">{{ $customer->name }}</h3>
                <p class="text-xs text-slate-500 font-mono">{{ $customer->customer_code }} • {{ $customer->whatsapp_number }}</p>
                <p class="text-xs text-slate-500">{{ $customer->address ?: 'Home Delivery' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div class="p-4 bg-slate-50 rounded-2xl">
                <span class="text-xs text-slate-500 block">Total Volume</span>
                <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ $bill->total_milk_quantity }} L</span>
            </div>
            <div class="p-4 bg-slate-50 rounded-2xl">
                <span class="text-xs text-slate-500 block">Milk Charges</span>
                <span class="text-xl font-extrabold text-slate-900 mt-1 block">₹{{ number_format($bill->milk_charges, 2) }}</span>
            </div>
            <div class="p-4 bg-slate-50 rounded-2xl">
                <span class="text-xs text-slate-500 block">Previous Balance</span>
                <span class="text-xl font-extrabold text-slate-900 mt-1 block">₹{{ number_format($bill->previous_balance, 2) }}</span>
            </div>
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200">
                <span class="text-xs text-emerald-700 font-bold block">Total Amount Due</span>
                <span class="text-xl font-extrabold text-emerald-700 mt-1 block">₹{{ number_format($bill->amount_due, 2) }}</span>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <h4 class="text-sm font-bold text-slate-900 mb-3">Itemized Delivery Snapshot</h4>
            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Type</th>
                            <th class="py-2.5 px-4">Litres</th>
                            <th class="py-2.5 px-4">Rate</th>
                            <th class="py-2.5 px-4">Daily Cost</th>
                            <th class="py-2.5 px-4">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if(isset($snapshot['days']))
                            @foreach($snapshot['days'] as $day)
                                <tr class="{{ $day['is_before_reg'] ? 'opacity-40 bg-slate-50' : '' }}">
                                    <td class="py-2.5 px-4 font-semibold text-slate-800">{{ $day['date'] }}</td>
                                    <td class="py-2.5 px-4 font-medium uppercase text-[10px]">{{ $day['type'] }}</td>
                                    <td class="py-2.5 px-4 font-bold">{{ $day['actual_quantity'] }} L</td>
                                    <td class="py-2.5 px-4">₹{{ number_format($day['rate'], 2) }}</td>
                                    <td class="py-2.5 px-4 font-bold text-slate-900">₹{{ number_format($day['charge'], 2) }}</td>
                                    <td class="py-2.5 px-4 text-slate-400 italic">{{ $day['note'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($settings->bill_footer)
            <div class="border-t border-slate-100 pt-4 text-center text-xs text-slate-400 italic">
                {{ $settings->bill_footer }}
            </div>
        @endif
    </div>
</div>

<script>
async function handleWhatsAppShare() {
    const defaultUrl = @json($whatsAppUrl);
    const downloadPdfUrl = @json(route('bills.pdf.download', $bill->id));
    const billTitle = @json('Bill_' . $bill->bill_number . '_' . $bill->billing_month);
    const messageText = @json($whatsAppMessage);

    if (navigator.share && navigator.canShare) {
        try {
            const res = await fetch(downloadPdfUrl);
            const blob = await res.blob();
            const file = new File([blob], billTitle + '.pdf', { type: 'application/pdf' });
            if (navigator.canShare({ files: [file] })) {
                await navigator.share({
                    files: [file],
                    title: billTitle,
                    text: messageText
                });
                return;
            }
        } catch (err) {}
    }

    window.open(defaultUrl, '_blank');
}
</script>
@endsection
