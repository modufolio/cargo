<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
        'driver_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
