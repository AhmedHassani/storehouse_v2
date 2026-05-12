<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxyWebhookLog extends Model
{
    protected $fillable = [
        'boxy_uid',
        'event_type',
        'payload',
        'headers',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
    ];
}
