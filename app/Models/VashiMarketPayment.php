<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VashiMarketPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_name',
        'payment_type',
        'transaction_id',
        'cheque_no',
        'receipt_no',
        'paid_date',
        'total_bills_amount',
        'total_paid_amount',
        'total_difference',
        'notes',
    ];

    protected $casts = [
        'paid_date' => 'date',
        'total_bills_amount' => 'decimal:2',
        'total_paid_amount' => 'decimal:2',
        'total_difference' => 'decimal:2',
    ];

    public function allocations()
    {
        return $this->hasMany(VashiMarketPaymentAllocation::class);
    }

    public function bills()
    {
        return $this->belongsToMany(
            VashiMarketBill::class,
            'vashi_market_payment_allocations',
            'vashi_market_payment_id',
            'vashi_market_bill_id'
        )->withPivot(['bill_amount', 'allocated_amount', 'interest_difference', 'interest_percentage'])
            ->withTimestamps();
    }
}
