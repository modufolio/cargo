<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pickup extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the items for the pickup data.
     */
    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }
}
