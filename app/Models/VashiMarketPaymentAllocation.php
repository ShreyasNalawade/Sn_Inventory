<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VashiMarketPaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vashi_market_payment_id',
        'vashi_market_bill_id',
        'bill_amount',
        'allocated_amount',
        'interest_difference',
        'interest_percentage',
    ];

    protected $casts = [
        'bill_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'interest_difference' => 'decimal:2',
        'interest_percentage' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(VashiMarketPayment::class, 'vashi_market_payment_id');
    }

    public function bill()
    {
        return $this->belongsTo(VashiMarketBill::class, 'vashi_market_bill_id');
    }
}
