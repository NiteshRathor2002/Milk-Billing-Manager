<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'customer_id',
        'billing_month',
        'period_start_date',
        'period_end_date',
        'total_milk_quantity',
        'milk_charges',
        'previous_balance',
        'payments_credited',
        'amount_due',
        'itemized_snapshot',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start_date' => 'date',
            'period_end_date' => 'date',
            'total_milk_quantity' => 'decimal:2',
            'milk_charges' => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'payments_credited' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'itemized_snapshot' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refreshFinancials(): void
    {
        $startDate = $this->period_start_date ? $this->period_start_date->format('Y-m-d') : null;
        $endDate = $this->period_end_date ? $this->period_end_date->format('Y-m-d') : null;

        $credited = (float) Payment::where('customer_id', $this->customer_id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('bill_id', $this->id);
                if ($startDate && $endDate) {
                    $q->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->whereNull('bill_id')
                            ->whereBetween('payment_date', [$startDate, $endDate]);
                    });
                }
            })
            ->sum('amount');

        $totalCharges = (float) $this->milk_charges + (float) $this->previous_balance;
        $amountDue = max(0, round($totalCharges - $credited, 2));

        $status = 'unpaid';
        if ($amountDue <= 0 && $credited > 0) {
            $status = 'paid';
        } elseif ($credited > 0 && $amountDue > 0) {
            $status = 'partially_paid';
        }

        $this->update([
            'payments_credited' => $credited,
            'amount_due' => $amountDue,
            'status' => $status,
        ]);
    }
}
