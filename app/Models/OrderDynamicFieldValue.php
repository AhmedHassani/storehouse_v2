<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDynamicFieldValue extends Model
{
    protected $fillable = [
        'order_id',
        'field_id',
        'field_value'
    ];

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the field definition
     */
    public function field()
    {
        return $this->belongsTo(OrderDynamicField::class, 'field_id');
    }
}
