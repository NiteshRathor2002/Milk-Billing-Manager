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
}
