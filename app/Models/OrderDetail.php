<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'product_id',
        'order_id',
        'price',
        'discount_on_product',
        'discount_type',
        'quantity',
        'tax_amount',
        'variant',
        'variation',
        'add_on_ids',
        'add_on_qtys',
        'add_on_prices',
        'add_on_taxes',
        'unit',
        'is_stock_decreased'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'order_id' => 'integer',
        'price' => 'float',
        'discount_on_product' => 'float',
        'quantity' => 'integer',
        'tax_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Review::class, 'order_id','order_id');
    }
}
