<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyDelivery;
use App\Models\AppSetting;
use App\Services\BillingCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeliveryCalendarController extends Controller
{
    public function show($customerId, Request $request, BillingCalculationService $billingService)
    {
        $customer = Customer::findOrFail($customerId);

        $now = Carbon::now();
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);

        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }

        $calc = $billingService->getMonthlyCalculation($customer, $year, $month);

        $prevMonthDate = Carbon::createFromDate($year, $month, 1)->subMonth();
        $nextMonthDate = Carbon::createFromDate($year, $month, 1)->addMonth();

        $firstDayOfWeek = (int) Carbon::createFromDate($year, $month, 1)->dayOfWeek;
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        $settings = AppSetting::getSettings();

        return view('calendar.show', [
            'customer' => $customer,
            'calc' => $calc,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'firstDayOfWeek' => $firstDayOfWeek,
            'prevYear' => $prevMonthDate->year,
            'prevMonth' => $prevMonthDate->month,
            'nextYear' => $nextMonthDate->year,
            'nextMonth' => $nextMonthDate->month,
            'settings' => $settings,
        ]);
    }

    public function saveEntry(Request $request, BillingCalculationService $billingService)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
            'delivery_type' => 'required|in:normal,holiday,reduced,extra,reset',
            'actual_quantity' => 'nullable|numeric|min:0',
            'extra_quantity' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $date = $validated['delivery_date'];
        $type = $validated['delivery_type'];

        if ($type === 'reset') {
            DailyDelivery::where('customer_id', $customer->id)
                ->where('delivery_date', $date)
                ->delete();

            if ($request->wantsJson()) {
                $dateObj = Carbon::parse($date);
                $calc = $billingService->getMonthlyCalculation($customer, $dateObj->year, $dateObj->month);
                return response()->json([
                    'success' => true,
                    'message' => 'Delivery reset to default quantity.',
                    'calc' => $calc,
                ]);
            }

            return back()->with('success', 'Entry reset to customer default quantity.');
        }

        $defaultQty = (float) $customer->daily_quantity;
        $rate = (float) $customer->rate_per_litre;
        $actualQty = $defaultQty;
        $extraQty = 0.00;
        $charge = round($actualQty * $rate, 2);

        if ($type === 'holiday') {
            $actualQty = 0.00;
            $charge = 0.00;
        } elseif ($type === 'reduced') {
            $actualQty = (float) ($validated['actual_quantity'] ?? 0.00);
            $charge = round($actualQty * $rate, 2);
        } elseif ($type === 'extra') {
            $extraQty = (float) ($validated['extra_quantity'] ?? 0.00);
            $actualQty = $defaultQty + $extraQty;
            $charge = round($actualQty * $rate, 2);
        }

        DailyDelivery::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'delivery_date' => $date,
            ],
            [
                'delivery_type' => $type,
                'default_quantity' => $defaultQty,
                'actual_quantity' => $actualQty,
                'extra_quantity' => $extraQty,
                'rate_applied' => $rate,
                'daily_charge' => $charge,
                'note' => $validated['note'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            $dateObj = Carbon::parse($date);
            $calc = $billingService->getMonthlyCalculation($customer, $dateObj->year, $dateObj->month);
            return response()->json([
                'success' => true,
                'message' => 'Delivery updated successfully.',
                'calc' => $calc,
            ]);
        }

        return back()->with('success', 'Delivery updated successfully.');
    }
}
