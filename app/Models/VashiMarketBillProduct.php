<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VashiMarketBillProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'vashi_market_bill_id',
        'product_name',
        'brand_name',
        'num_bags',
        'bag_size',
        'total_kg',
        'rate',
        'product_amount',
    ];

    public function bill()
    {
        return $this->belongsTo(VashiMarketBill::class, 'vashi_market_bill_id');
    }
}