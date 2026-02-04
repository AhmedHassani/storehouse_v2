<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLocationCity extends Model
{
    use HasFactory;

    protected $table = 'delivery_location_city';
    protected $guarded = ['id'];
}
