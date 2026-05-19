<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminBroadcast extends Model
{
    protected $table = 'admin_broadcasts';

    protected $fillable = [
        'title',
        'message',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active broadcasts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
