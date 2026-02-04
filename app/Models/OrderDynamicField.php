<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDynamicField extends Model
{
    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'field_options',
        'default_value',
        'is_required',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get field options as array
     */
    public function getOptionsArrayAttribute()
    {
        if (in_array($this->field_type, ['select', 'radio']) && $this->field_options) {
            return json_decode($this->field_options, true) ?? [];
        }
        return [];
    }

    /**
     * Scope for active fields only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->orderBy('sort_order');
    }

    /**
     * Get field values for orders
     */
    public function values()
    {
        return $this->hasMany(OrderDynamicFieldValue::class, 'field_id');
    }
}
