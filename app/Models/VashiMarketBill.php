<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VashiMarketBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_date',
        'received_date',
        'bill_no',
        'party_name',
        'dalal',
        'transport_name',
        'total_bill_amount',
        'is_paid',
        'payment_type',
        'transaction_id',
        'cheque_no',
        'receipt_no',
        'paid_date',
        'paid_amount',
    ];

    public function products()
    {
        return $this->hasMany(VashiMarketBillProduct::class);
    }
}