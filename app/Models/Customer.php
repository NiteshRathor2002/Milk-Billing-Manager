<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'name',
        'whatsapp_number',
        'daily_quantity',
        'rate_per_litre',
        'registration_date',
        'previous_balance',
        'address',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'daily_quantity' => 'decimal:2',
            'rate_per_litre' => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'registration_date' => 'date',
        ];
    }

    public function dailyDeliveries(): HasMany
    {
        return $this->hasMany(DailyDelivery::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getEffectiveBalanceAttribute(): float
    {
        $totalDebits = (float) $this->previous_balance + (float) $this->bills()->sum('milk_charges');
        $totalCredits = (float) $this->payments()->sum('amount');
        return round($totalDebits - $totalCredits, 2);
    }
}
