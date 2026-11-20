<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class TransactionDetail extends Model
{
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'price',
    ];

    /**
     * product
     * 
     * @return void
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}