@extends('layouts.app')

@section('title', 'Calendar - ' . $customer->name)
@section('header_title', 'Daily Milk Delivery Calendar')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    selectedDate: '',
    selectedDay: '',
    deliveryType: 'normal',
    actualQty: '{{ $customer->daily_quantity }}',
    extraQty: '0.00',
    note: '',
    defaultQty: {{ $customer->daily_quantity }},
    rate: {{ $customer->rate_per_litre }},
    openModal(day) {
        if (day.is_before_reg) return;
        this.selectedDate = day.date;
        this.selectedDay = day.day + ' ' + day.day_name;
        this.deliveryType = day.type === 'normal' ? 'normal' : day.type;
        this.actualQty = day.actual_quantity;
        this.extraQty = day.extra_quantity || '0.00';
        this.note = day.note || '';
        this.modalOpen = true;
    }
}">

    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('customers.show', $customer->id) }}" class="text-xs text-slate-400 hover:text-slate-600 font-bold">
                    ← {{ $customer->customer_code }}
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Delivery Ledger</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $customer->name }}</h2>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span>Default: <strong class="text-slate-800">{{ $customer->daily_quantity }} L/day</strong></span>
                <span>•</span>
                <span>Rate: <strong class="text-slate-800">₹{{ number_format($customer->rate_per_litre, 2) }}/L</strong></span>
                <span>•</span>
                <span>Registered: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}</strong></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center bg-slate-100 rounded-xl p-1 border border-slate-200">
                <a href="{{ route('calendar.show', ['customerId' => $customer->id, 'year' => $prevYear, 'month' => $prevMonth]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 hover:bg-white hover:shadow-xs transition-all">
                    ‹ Previous
                </a>
                <span class="px-4 py-1.5 text-xs font-extrabold text-slate-900 min-w-32 text-center">
                    {{ $monthName }}
                </span>
                <a href="{{ route('calendar.show', ['customerId' => $customer->id, 'year' => $nextYear, 'month' => $nextMonth]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 hover:bg-white hover:shadow-xs transition-all">
                    Next ›
                </a>
            </div>

            <a href="{{ route('billing.preview', ['customerId' => $customer->id, 'year' => $year, 'month' => $month]) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition-colors">
                🧾 Preview & Bill
            </a>
        </div>
    </div>

    @if($calc['applicable'])
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-4 md:p-6 text-white shadow-md">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-center divide-y sm:divide-y-0 sm:divide-x divide-slate-700">
                <div class="p-2">
                    <span class="text-xs text-slate-400 block">Total Volume</span>
                    <span class="text-xl md:text-2xl font-extrabold text-emerald-400">{{ $calc['total_milk_quantity'] }} L</span>
                </div>
                <div class="p-2">
                    <span class="text-xs text-slate-400 block">Milk Charges</span>
                    <span class="text-xl md:text-2xl font-extrabold text-white">₹{{ number_format($calc['milk_charges'], 2) }}</span>
                </div>
                <div class="p-2">
                    <span class="text-xs text-slate-400 block">Previous Balance</span>
                    <span class="text-xl md:text-2xl font-extrabold text-white">₹{{ number_format($calc['previous_balance'], 2) }}</span>
                </div>
                <div class="p-2">
                    <span class="text-xs text-slate-400 block">Payments Credited</span>
                    <span class="text-xl md:text-2xl font-extrabold text-emerald-400">₹{{ number_format($calc['payments'], 2) }}</span>
                </div>
                <div class="p-2 col-span-2 sm:col-span-1">
                    <span class="text-xs text-slate-400 block">Total Amount Due</span>
                    <span class="text-xl md:text-2xl font-extrabold text-amber-300">₹{{ number_format($calc['amount_due'], 2) }}</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-700/80 flex flex-wrap items-center justify-between text-xs text-slate-400">
                <div class="flex items-center space-x-3">
                    <span>Days Summary:</span>
                    <span class="text-emerald-400 font-semibold">{{ $calc['normal_days'] }} Normal</span>
                    <span class="text-rose-400 font-semibold">{{ $calc['holiday_days'] }} Holidays</span>
                    <span class="text-amber-400 font-semibold">{{ $calc['reduced_days'] }} Reduced</span>
                    <span class="text-sky-400 font-semibold">{{ $calc['extra_days'] }} Extra</span>
                </div>
                <span class="text-slate-400 italic">Click on any active calendar day below to record exceptions</span>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
            {{ $calc['reason'] }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 md:p-6 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs border-b border-slate-100 pb-3">
            <span class="font-bold text-slate-700">Delivery Status Legend:</span>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span class="text-slate-600 font-medium">Normal</span></span>
                <span class="inline-flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span><span class="text-slate-600 font-medium">Holiday (0L)</span></span>
                <span class="inline-flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-slate-600 font-medium">Reduced</span></span>
                <span class="inline-flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-sky-500"></span><span class="text-slate-600 font-medium">Extra Milk</span></span>
                <span class="inline-flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-slate-300"></span><span class="text-slate-600 font-medium">Pre-Registration</span></span>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-2 md:gap-3 text-center">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                <div class="py-2 text-xs font-extrabold uppercase tracking-wider text-slate-400">
                    {{ $weekday }}
                </div>
            @endforeach

            @for($i = 0; $i < $firstDayOfWeek; $i++)
                <div class="min-h-24 md:min-h-28 rounded-2xl bg-slate-50/50 border border-transparent"></div>
            @endfor

            @foreach($calc['days'] as $day)
                <div @click="openModal({{ json_encode($day) }})"
                     class="min-h-24 md:min-h-28 rounded-2xl border p-2 md:p-3 flex flex-col justify-between text-left transition-all duration-150 relative group
                     {{ $day['is_before_reg'] ? 'bg-slate-100/60 border-slate-200/60 text-slate-400 cursor-not-allowed' : 'cursor-pointer hover:shadow-md hover:scale-[1.02] bg-white ' }}
                     {{ $day['type'] === 'holiday' ? 'border-rose-300 bg-rose-50/30' : '' }}
                     {{ $day['type'] === 'reduced' ? 'border-amber-300 bg-amber-50/30' : '' }}
                     {{ $day['type'] === 'extra' ? 'border-sky-300 bg-sky-50/30' : '' }}
                     {{ $day['type'] === 'normal' ? 'border-slate-200 hover:border-emerald-300' : '' }}
                     ">

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-extrabold {{ $day['is_before_reg'] ? 'text-slate-400' : 'text-slate-800' }}">
                            {{ $day['day'] }}
                        </span>
                        @if($day['note'])
                            <span class="text-xs" title="{{ $day['note'] }}">📝</span>
                        @endif
                    </div>

                    <div class="mt-1">
                        @if($day['is_before_reg'])
                            <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-200 text-slate-500">
                                Pre-Reg
                            </span>
                            <div class="text-[11px] text-slate-400 mt-1">₹0.00</div>
                        @elseif($day['type'] === 'holiday')
                            <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-700">
                                Holiday (0L)
                            </span>
                            <div class="text-[11px] font-bold text-rose-600 mt-1">₹0.00</div>
                        @elseif($day['type'] === 'reduced')
                            <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                                Reduced: {{ $day['actual_quantity'] }}L
                            </span>
                            <div class="text-[11px] font-bold text-amber-700 mt-1">₹{{ number_format($day['charge'], 2) }}</div>
                        @elseif($day['type'] === 'extra')
                            <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800">
                                Extra: {{ $day['actual_quantity'] }}L
                            </span>
                            <div class="text-[11px] font-bold text-sky-700 mt-1">₹{{ number_format($day['charge'], 2) }}</div>
                        @else
                            <span class="inline-block px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-700">
                                Normal ({{ $day['actual_quantity'] }}L)
                            </span>
                            <div class="text-[11px] font-bold text-slate-700 mt-1">₹{{ number_format($day['charge'], 2) }}</div>
                        @endif
                    </div>

                    @if(!$day['is_before_reg'])
                        <div class="text-[10px] text-slate-400 text-right opacity-0 group-hover:opacity-100 transition-opacity">
                            Edit ✎
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100" @click.away="modalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h4 class="text-base font-bold text-slate-900">Record Delivery Exception</h4>
                    <span class="text-xs font-semibold text-emerald-600" x-text="selectedDate + ' (' + selectedDay + ')'"></span>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold p-1">✕</button>
            </div>

            <form action="{{ route('calendar.entry.save') }}" method="POST" class="space-y-4 pt-4">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="delivery_date" :value="selectedDate">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Delivery Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50" :class="deliveryType === 'normal' ? 'border-emerald-500 bg-emerald-50/40 text-emerald-900 font-bold' : ''">
                            <input type="radio" name="delivery_type" value="normal" x-model="deliveryType" class="text-emerald-600">
                            <span class="ml-2 text-xs">Normal (Default)</span>
                        </label>
                        <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50" :class="deliveryType === 'holiday' ? 'border-rose-500 bg-rose-50/40 text-rose-900 font-bold' : ''">
                            <input type="radio" name="delivery_type" value="holiday" x-model="deliveryType" class="text-rose-600">
                            <span class="ml-2 text-xs">Holiday (0 Litres)</span>
                        </label>
                        <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50" :class="deliveryType === 'reduced' ? 'border-amber-500 bg-amber-50/40 text-amber-900 font-bold' : ''">
                            <input type="radio" name="delivery_type" value="reduced" x-model="deliveryType" class="text-amber-600">
                            <span class="ml-2 text-xs">Reduced Milk</span>
                        </label>
                        <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50" :class="deliveryType === 'extra' ? 'border-sky-500 bg-sky-50/40 text-sky-900 font-bold' : ''">
                            <input type="radio" name="delivery_type" value="extra" x-model="deliveryType" class="text-sky-600">
                            <span class="ml-2 text-xs">Extra Milk</span>
                        </label>
                    </div>
                </div>

                <div x-show="deliveryType === 'reduced'">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Actual Delivered Quantity (Litres)</label>
                    <input type="number" step="any" min="0" name="actual_quantity" x-model="actualQty"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm font-bold focus:bg-white focus:ring-2 focus:ring-emerald-500">
                    <span class="text-xs text-slate-400 mt-1 block">Default was <strong x-text="defaultQty + ' L'"></strong></span>
                </div>

                <div x-show="deliveryType === 'extra'">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Additional Extra Quantity (Litres)</label>
                    <input type="number" step="any" min="0" name="extra_quantity" x-model="extraQty"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm font-bold focus:bg-white focus:ring-2 focus:ring-emerald-500">
                    <span class="text-xs text-slate-400 mt-1 block">Total will be: <strong x-text="(parseFloat(defaultQty) + (parseFloat(extraQty) || 0)).toFixed(2) + ' Litres'"></strong></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Optional Reason / Note</label>
                    <input type="text" name="note" x-model="note" placeholder="e.g. Guests arrived, festival holiday"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="submit" name="delivery_type" value="reset" class="text-xs text-rose-600 font-bold hover:underline">
                        Reset to Default
                    </button>
                    <div class="flex items-center space-x-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl text-slate-600 text-sm font-semibold hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs">
                            Save Entry
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
