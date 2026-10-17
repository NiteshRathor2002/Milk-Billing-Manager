<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'business_phone',
        'business_email',
        'business_address',
        'upi_id',
        'bill_title',
        'bill_footer',
        'currency_symbol',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'business_name' => 'Shree Krishna Dairy Farm',
            'business_phone' => '+91 9876543210',
            'business_email' => 'admin@milkmanager.com',
            'business_address' => 'Main Market, Dairy Road, Sector 12, India',
            'upi_id' => 'dairy@upi',
            'bill_title' => 'Monthly Milk Delivery Invoice',
            'bill_footer' => 'Thank you for choosing our fresh dairy service! Please clear pending dues on or before the 10th of every month.',
            'currency_symbol' => '₹',
        ]);
    }
}
