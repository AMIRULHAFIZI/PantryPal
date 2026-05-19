<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PantryItem extends Model
{
    protected $fillable = ['user_id', 'item_name', 'quantity', 'unit', 'expiry_date', 'ripeness_info', 'category'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
