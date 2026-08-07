<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RipenessScan extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'item_name',
        'ripeness_level',
        'ripeness_score',
        'color_description',
        'shelf_life_days',
        'recommendation',
        'storage_tip',
        'is_success',
    ];

    protected $casts = [
        'is_success' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
