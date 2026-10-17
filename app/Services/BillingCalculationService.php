<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DailyDelivery;
use Carbon\Carbon;

class BillingCalculationService
{
    public function getMonthlyCalculation(Customer $customer, int $year, int $month): array
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->startOfDay();
        $regDate = Carbon::parse($customer->registration_date)->startOfDay();

        if ($regDate->gt($endOfMonth)) {
            return [
                'applicable' => false,
                'reason' => 'Customer registered after this billing month',
                'period_start' => null,
                'period_end' => null,
                'days' => [],
                'total_milk_quantity' => 0.00,
                'milk_charges' => 0.00,
                'previous_balance' => (float) $customer->previous_balance,
                'payments' => 0.00,
                'amount_due' => 0.00,
                'normal_days' => 0,
                'holiday_days' => 0,
                'reduced_days' => 0,
                'extra_days' => 0,
            ];
        }

        $effectiveStart = $regDate->gt($startOfMonth) ? $regDate->copy() : $startOfMonth->copy();
        $effectiveEnd = $endOfMonth->copy();

        $deliveries = DailyDelivery::where('customer_id', $customer->id)
            ->whereBetween('delivery_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->delivery_date)->format('Y-m-d'));

        $days = [];
        $totalQuantity = 0.0;
        $totalCharges = 0.0;
        $normalCount = 0;
        $holidayCount = 0;
        $reducedCount = 0;
        $extraCount = 0;

        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            $dateString = $current->format('Y-m-d');
            $isBeforeRegistration = $current->lt($regDate);
            $hasCustomEntry = $deliveries->has($dateString);

            if ($isBeforeRegistration) {
                $days[] = [
                    'date' => $dateString,
                    'day' => (int) $current->format('j'),
                    'day_name' => $current->format('D'),
                    'is_before_reg' => true,
                    'type' => 'before_registration',
                    'default_quantity' => 0.0,
                    'actual_quantity' => 0.0,
                    'extra_quantity' => 0.0,
                    'rate' => (float) $customer->rate_per_litre,
                    'charge' => 0.0,
                    'note' => 'Prior to registration',
                ];
            } else {
                if ($hasCustomEntry) {
                    $entry = $deliveries->get($dateString);
                    $type = $entry->delivery_type;
                    $actual = (float) $entry->actual_quantity;
                    $extra = (float) $entry->extra_quantity;
                    $rate = (float) $entry->rate_applied;
                    $charge = (float) $entry->daily_charge;
                    $note = $entry->note;

                    if ($type === 'holiday') {
                        $holidayCount++;
                    } elseif ($type === 'reduced') {
                        $reducedCount++;
                    } elseif ($type === 'extra') {
                        $extraCount++;
                    } else {
                        $normalCount++;
                    }
                } else {
                    $type = 'normal';
                    $actual = (float) $customer->daily_quantity;
                    $extra = 0.0;
                    $rate = (float) $customer->rate_per_litre;
                    $charge = round($actual * $rate, 2);
                    $note = null;
                    $normalCount++;
                }

                $totalQuantity += $actual;
                $totalCharges += $charge;

                $days[] = [
                    'date' => $dateString,
                    'day' => (int) $current->format('j'),
                    'day_name' => $current->format('D'),
                    'is_before_reg' => false,
                    'type' => $type,
                    'default_quantity' => (float) $customer->daily_quantity,
                    'actual_quantity' => $actual,
                    'extra_quantity' => $extra,
                    'rate' => $rate,
                    'charge' => $charge,
                    'note' => $note,
                ];
            }

            $current->addDay();
        }

        $billingMonthStr = sprintf('%04d-%02d', $year, $month);
        $previousBalance = $this->calculatePreviousBalanceForMonth($customer, $year, $month);

        $paymentsThisMonth = (float) $customer->payments()
            ->whereBetween('payment_date', [$effectiveStart->format('Y-m-d'), $effectiveEnd->format('Y-m-d')])
            ->sum('amount');

        $amountDue = round($totalCharges + $previousBalance - $paymentsThisMonth, 2);

        return [
            'applicable' => true,
            'billing_month' => $billingMonthStr,
            'period_start' => $effectiveStart->format('Y-m-d'),
            'period_end' => $effectiveEnd->format('Y-m-d'),
            'days' => $days,
            'total_milk_quantity' => round($totalQuantity, 2),
            'milk_charges' => round($totalCharges, 2),
            'previous_balance' => round($previousBalance, 2),
            'payments' => round($paymentsThisMonth, 2),
            'amount_due' => $amountDue,
            'normal_days' => $normalCount,
            'holiday_days' => $holidayCount,
            'reduced_days' => $reducedCount,
            'extra_days' => $extraCount,
            'rate_per_litre' => (float) $customer->rate_per_litre,
        ];
    }

    public function calculatePreviousBalanceForMonth(Customer $customer, int $year, int $month): float
    {
        $currentMonthStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $previousMonth = $currentMonthStart->copy()->subMonth();
        $previousMonthStr = $previousMonth->format('Y-m');

        $pastBill = $customer->bills()
            ->where('billing_month', '<', sprintf('%04d-%02d', $year, $month))
            ->latest('billing_month')
            ->first();

        if ($pastBill) {
            $paymentsAfterPastBill = (float) $customer->payments()
                ->where('payment_date', '>', $pastBill->period_end_date)
                ->where('payment_date', '<', $currentMonthStart->format('Y-m-d'))
                ->sum('amount');

            return (float) $pastBill->amount_due - $paymentsAfterPastBill;
        }

        $regDate = Carbon::parse($customer->registration_date)->startOfDay();
        $paymentsBeforeThisMonth = (float) $customer->payments()
            ->where('payment_date', '<', $currentMonthStart->format('Y-m-d'))
            ->sum('amount');

        return max(0, (float) $customer->previous_balance - $paymentsBeforeThisMonth);
    }
}
