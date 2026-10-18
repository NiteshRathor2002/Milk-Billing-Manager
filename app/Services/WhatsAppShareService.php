<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\AppSetting;
use Carbon\Carbon;

class WhatsAppShareService
{
    public function generateMessageText(Bill $bill): string
    {
        $customer = $bill->customer;
        $settings = AppSetting::getSettings();
        $monthName = Carbon::createFromFormat('Y-m', $bill->billing_month)->format('F Y');

        $currency = $settings->currency_symbol ?? '₹';
        $business = $settings->business_name ?? 'Dairy Milk Supply';

        $message = "*INVOICE - {$business}*\n";
        $message .= "----------------------------------------\n";
        $message .= "Dear {$customer->name} ({$customer->customer_code}),\n\n";
        $message .= "Here is your milk delivery statement for *{$monthName}*.\n\n";

        $message .= "*Invoice Details:*\n";
        $message .= "• Invoice No: {$bill->bill_number}\n";
        $message .= "• Billing Period: {$monthName}\n";
        $message .= "• Total Milk Delivered: {$bill->total_milk_quantity} Litres\n";
        $message .= "• Milk Charges: {$currency}" . number_format((float) $bill->milk_charges, 2) . "\n";

        if ((float) $bill->previous_balance > 0) {
            $message .= "• Previous Balance: {$currency}" . number_format((float) $bill->previous_balance, 2) . "\n";
        }

        if ((float) $bill->payments_credited > 0) {
            $message .= "• Payments Credited: {$currency}" . number_format((float) $bill->payments_credited, 2) . "\n";
        }

        $message .= "----------------------------------------\n";
        $message .= "*Total Amount Due: {$currency}" . number_format((float) $bill->amount_due, 2) . "*\n";
        $message .= "----------------------------------------\n\n";

        $pdfUrl = route('bills.public.pdf', $bill->bill_number);
        $message .= "*Download Official Invoice (PDF):*\n{$pdfUrl}\n\n";

        if (!empty($settings->upi_id)) {
            $message .= "*Payment Options:*\n";
            $message .= "Pay via UPI: {$settings->upi_id}\n\n";
        }

        $message .= "For any questions or support, please contact {$settings->business_phone}.\n";
        $message .= "Thank you for your business.";

        return $message;
    }

    public function generateWhatsAppUrl(Bill $bill): string
    {
        $customer = $bill->customer;

        $cleanPhone = preg_replace('/[^0-9]/', '', $customer->whatsapp_number);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $message = $this->generateMessageText($bill);

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message);
    }
}
