<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'delivery_date',
        'delivery_type',
        'default_quantity',
        'actual_quantity',
        'extra_quantity',
        'rate_applied',
        'daily_charge',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'default_quantity' => 'decimal:2',
            'actual_quantity' => 'decimal:2',
            'extra_quantity' => 'decimal:2',
            'rate_applied' => 'decimal:2',
            'daily_charge' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
