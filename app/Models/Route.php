<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'price' => 'float',
        'minimum_weight' => 'float'
    ];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }
}
