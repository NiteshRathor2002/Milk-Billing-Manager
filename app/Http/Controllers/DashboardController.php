<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyDelivery;
use App\Models\Bill;
use App\Models\AppSetting;
use App\Services\BillingCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(BillingCalculationService $billingService)
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();

        $activeList = Customer::where('status', 'active')->get();
        $totalEstimatedLitres = 0.0;
        $totalEstimatedCharges = 0.0;

        foreach ($activeList as $cust) {
            $calc = $billingService->getMonthlyCalculation($cust, $year, $month);
            if ($calc['applicable']) {
                $totalEstimatedLitres += $calc['total_milk_quantity'];
                $totalEstimatedCharges += $calc['milk_charges'];
            }
        }

        $allCustomers = Customer::all();
        $totalOutstanding = 0.0;
        foreach ($allCustomers as $cust) {
            $totalOutstanding += $cust->effective_balance;
        }

        $recentExceptions = DailyDelivery::with('customer')
            ->orderBy('delivery_date', 'desc')
            ->take(7)
            ->get();

        $recentBills = Bill::with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $settings = AppSetting::getSettings();

        return view('dashboard.index', [
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'inactiveCustomers' => $inactiveCustomers,
            'totalEstimatedLitres' => round($totalEstimatedLitres, 1),
            'totalEstimatedCharges' => round($totalEstimatedCharges, 2),
            'totalOutstanding' => round($totalOutstanding, 2),
            'recentExceptions' => $recentExceptions,
            'recentBills' => $recentBills,
            'settings' => $settings,
            'currentMonthName' => $now->format('F Y'),
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }
}
