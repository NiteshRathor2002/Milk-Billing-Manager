<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\AppSetting;
use Carbon\Carbon;

class WhatsAppShareService
{
    public function generateWhatsAppUrl(Bill $bill): string
    {
        $customer = $bill->customer;
        $settings = AppSetting::getSettings();
        $monthName = Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y');

        $cleanPhone = preg_replace('/[^0-9]/', '', $customer->whatsapp_number);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $currency = $settings->currency_symbol ?? '₹';
        $business = $settings->business_name ?? 'Dairy Milk Supply';

        $message = "🥛 *{$business}* - Milk Bill\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "👤 *Customer:* {$customer->name} ({$customer->customer_code})\n";
        $message .= "📅 *Billing Period:* {$monthName}\n";
        $message .= "🧾 *Bill No:* {$bill->bill_number}\n\n";
        $message .= "📦 *Total Milk Delivered:* {$bill->total_milk_quantity} Litres\n";
        $message .= "🥛 *Monthly Milk Charges:* {$currency}{$bill->milk_charges}\n";

        if ((float) $bill->previous_balance > 0) {
            $message .= "⏳ *Previous Balance:* {$currency}{$bill->previous_balance}\n";
        }

        if ((float) $bill->payments_credited > 0) {
            $message .= "💳 *Payments Credited:* {$currency}{$bill->payments_credited}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💰 *Total Amount Due:* {$currency}{$bill->amount_due}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        if (!empty($settings->upi_id)) {
            $message .= "📲 *Pay via UPI:* {$settings->upi_id}\n\n";
        }

        $message .= "Thank you for your business! Please find your detailed bill attached.\n";
        $message .= "📞 Support: {$settings->business_phone}";

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message);
    }
}
