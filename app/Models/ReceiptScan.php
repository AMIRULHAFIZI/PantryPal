<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptScan extends Model
{
    protected $fillable = [
        'user_id',
        'total_items_found',
        'items_extracted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
