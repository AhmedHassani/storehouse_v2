<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'is_guest',
        'order_amount',
        'coupon_discount_amount',
        'coupon_discount_title',
        'payment_status',
        'order_status',
        'total_tax_amount',
        'payment_method',
        'transaction_reference',
        'delivery_address_id',
        'created_at',
        'updated_at',
        'checked',
        'delivery_man_id',
        'delivery_charge',
        'order_note',
        'coupon_code',
        'order_type',
        'branch_id',
        'callback',
        'extra_discount',
        'delivery_address',
        'bring_change_amount',
        'paid_amount',
        'sale_channel',
        'sale_agent',
        'is_organic',
        'video_link',
        'delivery_date',
        'agent_username',
        'boxy_uid',
        'boxy_platform_code',
    ];

    protected $casts = [
        'order_amount' => 'float',
        'coupon_discount_amount' => 'float',
        'total_tax_amount' => 'float',
        'delivery_address_id' => 'integer',
        'delivery_man_id' => 'integer',
        'delivery_charge' => 'float',
        'user_id' => 'integer',
        'delivery_address' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'bring_change_amount' => 'float',
        'paid_amount' => 'float',
        'is_organic' => 'boolean',
        'delivery_date' => 'date',

    ];

    public function dynamicValues()
    {
        return $this->hasMany(\App\Models\OrderDynamicFieldValue::class, 'order_id');
    }

    public function details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function delivery_man(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function delivery_address(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

    public function scopePos($query)
    {
        return $query->where('order_type', '=', 'pos');
    }

    public function scopeNotPos($query)
    {
        return $query->where('order_type', '!=', 'pos');
    }

    public function guest()
    {
        return $this->belongsTo(GuestUser::class, 'user_id');
    }

    public static function booted()
    {
        static::creating(function ($order) {
            // Generate a custom Order ID
            if (!$order->id) {
                // If order_id needs to follow a custom format like ORD-XXXX, based on the last inserted order
                $lastOrder = self::latest('id')->first();
                $order->id = $lastOrder ? $lastOrder->id + 1 : 100001; // Defaulting to 100000 if no last order exists
            }
        });
    }
}
