<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\AppSetting;
use App\Services\BillingCalculationService;
use App\Services\PdfInvoiceService;
use App\Services\WhatsAppShareService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $selectedMonth = $request->input('month', $now->format('Y-m'));

        $bills = Bill::with('customer')
            ->where('billing_month', $selectedMonth)
            ->orderBy('id', 'desc')
            ->paginate(15);

        foreach ($bills as $b) {
            $b->refreshFinancials();
        }

        $billedCustomerIds = Bill::where('billing_month', $selectedMonth)
            ->pluck('customer_id')
            ->toArray();

        $unbilledCustomers = Customer::where('status', 'active')
            ->whereNotIn('id', $billedCustomerIds)
            ->get();

        $settings = AppSetting::getSettings();

        return view('billing.index', [
            'bills' => $bills,
            'unbilledCustomers' => $unbilledCustomers,
            'selectedMonth' => $selectedMonth,
            'settings' => $settings,
        ]);
    }

    public function preview($customerId, $year, $month, BillingCalculationService $billingService)
    {
        $customer = Customer::findOrFail($customerId);
        $calc = $billingService->getMonthlyCalculation($customer, (int) $year, (int) $month);
        $existingBill = Bill::where('customer_id', $customer->id)
            ->where('billing_month', sprintf('%04d-%02d', $year, $month))
            ->first();

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $settings = AppSetting::getSettings();

        return view('billing.preview', [
            'customer' => $customer,
            'calc' => $calc,
            'year' => (int) $year,
            'month' => (int) $month,
            'monthName' => $monthName,
            'existingBill' => $existingBill,
            'settings' => $settings,
        ]);
    }

    public function generate(Request $request, BillingCalculationService $billingService)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $billingMonthStr = sprintf('%04d-%02d', $year, $month);

        $calc = $billingService->getMonthlyCalculation($customer, $year, $month);

        if (!$calc['applicable']) {
            return back()->with('error', $calc['reason'] ?? 'Cannot generate bill for this month.');
        }

        $existing = Bill::where('customer_id', $customer->id)
            ->where('billing_month', $billingMonthStr)
            ->first();

        $billNumber = $existing ? $existing->bill_number : sprintf('INV-%04d%02d-%03d', $year, $month, $customer->id);

        $bill = Bill::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'billing_month' => $billingMonthStr,
            ],
            [
                'bill_number' => $billNumber,
                'period_start_date' => $calc['period_start'],
                'period_end_date' => $calc['period_end'],
                'total_milk_quantity' => $calc['total_milk_quantity'],
                'milk_charges' => $calc['milk_charges'],
                'previous_balance' => $calc['previous_balance'],
                'payments_credited' => $calc['payments'],
                'amount_due' => $calc['amount_due'],
                'itemized_snapshot' => $calc,
                'status' => $calc['amount_due'] <= 0 && $calc['payments'] > 0 ? 'paid' : ($calc['payments'] > 0 ? 'partially_paid' : 'unpaid'),
                'generated_at' => now(),
            ]
        );

        return redirect()->route('bills.show', $bill->id)
            ->with('success', "Bill {$bill->bill_number} generated successfully!");
    }

    public function show($id, WhatsAppShareService $waService)
    {
        $bill = Bill::with(['customer', 'payments'])->findOrFail($id);
        $bill->refreshFinancials();
        $bill->refresh();

        $whatsAppUrl = $waService->generateWhatsAppUrl($bill);
        $whatsAppMessage = $waService->generateMessageText($bill);
        $settings = AppSetting::getSettings();

        return view('billing.show', [
            'bill' => $bill,
            'customer' => $bill->customer,
            'snapshot' => $bill->itemized_snapshot,
            'whatsAppUrl' => $whatsAppUrl,
            'whatsAppMessage' => $whatsAppMessage,
            'settings' => $settings,
        ]);
    }

    public function downloadPdf($id, PdfInvoiceService $pdfService)
    {
        $bill = Bill::with('customer')->findOrFail($id);
        return $pdfService->downloadPdf($bill);
    }

    public function streamPdf($id, PdfInvoiceService $pdfService)
    {
        $bill = Bill::with('customer')->findOrFail($id);
        return $pdfService->streamPdf($bill);
    }

    public function shareWhatsApp($id, WhatsAppShareService $waService)
    {
        $bill = Bill::with('customer')->findOrFail($id);
        $url = $waService->generateWhatsAppUrl($bill);
        return redirect()->away($url);
    }

    public function publicPdf(string $billNumber, PdfInvoiceService $pdfService)
    {
        $bill = Bill::with('customer')->where('bill_number', $billNumber)->firstOrFail();
        return $pdfService->streamPdf($bill);
    }
}
