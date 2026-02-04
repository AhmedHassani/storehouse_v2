<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'key',
        'name',
        'module',
        'description',
        'status',
    ];

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_permissions');
    }
}
