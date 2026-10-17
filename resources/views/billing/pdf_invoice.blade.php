<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $bill->bill_number }}</title>
    <style>
        @page { margin: 25px 30px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #1e293b; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 12px; margin-bottom: 15px; }
        .business-title { font-size: 18px; font-weight: bold; color: #059669; text-transform: uppercase; margin: 0 0 4px 0; }
        .business-meta { font-size: 10px; color: #64748b; margin: 0; }
        .invoice-title { font-size: 16px; font-weight: bold; color: #0f172a; text-align: right; margin: 0 0 4px 0; }
        .invoice-meta { font-size: 10px; color: #64748b; text-align: right; margin: 0; }
        .meta-table { width: 100%; margin-bottom: 15px; }
        .meta-col { width: 50%; vertical-align: top; }
        .box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; }
        .box-title { font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .customer-name { font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 3px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-table th { background-color: #f1f5f9; color: #475569; font-size: 10px; font-weight: bold; text-transform: uppercase; padding: 7px 10px; border: 1px solid #cbd5e1; }
        .summary-table td { padding: 7px 10px; border: 1px solid #cbd5e1; font-size: 11px; }
        .itemized-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px; }
        .itemized-table th { background-color: #059669; color: #ffffff; font-weight: bold; text-transform: uppercase; padding: 5px 6px; border: 1px solid #047857; text-align: left; }
        .itemized-table td { padding: 4px 6px; border: 1px solid #e2e8f0; }
        .itemized-table tr:nth-child(even) { background-color: #f8fafc; }
        .total-box { float: right; width: 260px; margin-top: 5px; }
        .total-table { width: 100%; border-collapse: collapse; }
        .total-table td { padding: 4px 8px; font-size: 11px; }
        .total-table .due-row { background-color: #ecfdf5; font-size: 13px; font-weight: bold; color: #065f46; border-top: 1px solid #059669; border-bottom: 1px solid #059669; }
        .footer { margin-top: 25px; padding-top: 10px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #64748b; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .badge { display: inline-block; padding: 1px 4px; font-size: 8px; font-weight: bold; border-radius: 3px; text-transform: uppercase; }
        .badge-normal { background: #d1fae5; color: #065f46; }
        .badge-holiday { background: #ffe4e6; color: #9f1239; }
        .badge-reduced { background: #fef3c7; color: #92400e; }
        .badge-extra { background: #e0f2fe; color: #0369a1; }
        .badge-before { background: #f1f5f9; color: #64748b; }
    </style>
</head>
<body>

    <table class="meta-table header">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <h1 class="business-title">{{ $settings->business_name }}</h1>
                <p class="business-meta">{{ $settings->business_phone }} | {{ $settings->business_email }}</p>
                <p class="business-meta">{{ $settings->business_address }}</p>
                @if($settings->upi_id)
                    <p class="business-meta font-bold" style="color: #059669; margin-top: 3px;">UPI ID for Payments: {{ $settings->upi_id }}</p>
                @endif
            </td>
            <td style="width: 40%; vertical-align: top;" class="text-right">
                <h2 class="invoice-title">{{ $settings->bill_title }}</h2>
                <p class="invoice-meta font-bold">Invoice #: {{ $bill->bill_number }}</p>
                <p class="invoice-meta">Billing Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y') }}</p>
                <p class="invoice-meta">Billing Period: {{ \Carbon\Carbon::parse($bill->period_start_date)->format('d M') }} to {{ \Carbon\Carbon::parse($bill->period_end_date)->format('d M Y') }}</p>
                <p class="invoice-meta">Issued Date: {{ \Carbon\Carbon::parse($bill->generated_at)->format('d M Y') }}</p>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-col" style="padding-right: 8px;">
                <div class="box">
                    <div class="box-title">Billed Customer</div>
                    <div class="customer-name">{{ $customer->name }}</div>
                    <div style="font-size: 10px; color: #475569;">
                        Customer ID: <strong>{{ $customer->customer_code }}</strong><br>
                        WhatsApp: <strong>{{ $customer->whatsapp_number }}</strong><br>
                        Address: {{ $customer->address ?: 'Registered Customer' }}
                    </div>
                </div>
            </td>
            <td class="meta-col" style="padding-left: 8px;">
                <div class="box">
                    <div class="box-title">Delivery Parameters</div>
                    <div style="font-size: 10px; color: #475569; margin-top: 4px;">
                        Daily Base Quantity: <strong>{{ $customer->daily_quantity }} Litres</strong><br>
                        Agreed Rate: <strong>Rs. {{ number_format($customer->rate_per_litre, 2) }} / Litre</strong><br>
                        Registration Date: {{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Volume Delivered</th>
                <th>Standard Rate</th>
                <th>Monthly Milk Charges</th>
                <th>Previous Outstanding</th>
                <th>Payments Credited</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center font-bold">
                <td>{{ $bill->total_milk_quantity }} Litres</td>
                <td>Rs. {{ number_format($snapshot['rate_per_litre'] ?? $customer->rate_per_litre, 2) }}</td>
                <td>Rs. {{ number_format($bill->milk_charges, 2) }}</td>
                <td>Rs. {{ number_format($bill->previous_balance, 2) }}</td>
                <td style="color: #059669;">- Rs. {{ number_format($bill->payments_credited, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div style="font-size: 9px; color: #475569; line-height: 1.5; padding: 8px; border-left: 3px solid #059669; background: #f8fafc;">
                    <strong>Delivery Legend:</strong><br>
                    • Normal: Standard daily quantity ({{ $customer->daily_quantity }}L)<br>
                    • Holiday: 0 Litres delivered (Rs. 0 charge)<br>
                    • Reduced / Extra: Recorded delivery deviations<br>
                    • Pre-Reg: Days prior to customer registration date
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="total-table">
                    <tr>
                        <td class="font-bold">Monthly Milk Charges:</td>
                        <td class="text-right">Rs. {{ number_format($bill->milk_charges, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">Previous Pending Balance:</td>
                        <td class="text-right">Rs. {{ number_format($bill->previous_balance, 2) }}</td>
                    </tr>
                    @if((float) $bill->payments_credited > 0)
                        <tr>
                            <td class="font-bold" style="color: #059669;">Less Payments Credited:</td>
                            <td class="text-right" style="color: #059669;">- Rs. {{ number_format($bill->payments_credited, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="due-row">
                        <td>TOTAL AMOUNT DUE:</td>
                        <td class="text-right">Rs. {{ number_format($bill->amount_due, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px;">
        <div style="font-size: 10px; font-weight: bold; color: #0f172a; margin-bottom: 5px;">Daily Delivery Itemized Log:</div>
        <table class="itemized-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Date</th>
                    <th style="width: 25%;">Delivery Type</th>
                    <th style="width: 15%; text-align: right;">Litres</th>
                    <th style="width: 15%; text-align: right;">Rate</th>
                    <th style="width: 15%; text-align: right;">Charge</th>
                    <th style="width: 10%;">Note</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($snapshot['days']))
                    @foreach($snapshot['days'] as $day)
                        <tr>
                            <td>{{ $day['date'] }} ({{ $day['day_name'] }})</td>
                            <td>
                                @if($day['is_before_reg'])
                                    <span class="badge badge-before">Pre-Reg</span>
                                @elseif($day['type'] === 'holiday')
                                    <span class="badge badge-holiday">Holiday (0L)</span>
                                @elseif($day['type'] === 'reduced')
                                    <span class="badge badge-reduced">Reduced</span>
                                @elseif($day['type'] === 'extra')
                                    <span class="badge badge-extra">Extra Milk</span>
                                @else
                                    <span class="badge badge-normal">Normal</span>
                                @endif
                            </td>
                            <td class="text-right font-bold">{{ $day['actual_quantity'] }} L</td>
                            <td class="text-right">Rs. {{ number_format($day['rate'], 2) }}</td>
                            <td class="text-right font-bold">Rs. {{ number_format($day['charge'], 2) }}</td>
                            <td>{{ $day['note'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>{{ $settings->bill_footer ?: 'Thank you for your business! Please clear monthly dues by the 10th.' }}</p>
        <p>This is a computer-generated invoice from Milk Billing Manager.</p>
    </div>

</body>
</html>
