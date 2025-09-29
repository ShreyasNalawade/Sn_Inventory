<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand_name',
        'purchase_price',
        'size_750ml',
        'size_1L',
        'size_3L',
        'size_5L',
        'size_15L_tin',
        'size_15L_jar',
        'type_product',
    ];
}
