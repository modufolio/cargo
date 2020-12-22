<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Unit extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $guarded = [];

    /**
     * Get the items with unit data.
     */
    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

    public function getCreatedAtAttribute($value)
    {
        $data = Carbon::parse($value)->format('Y-m-d h:m:s');
        return $data;
    }

    public function getUpdatedAtAttribute($value)
    {
        $data = Carbon::parse($value)->format('Y-m-d h:m:s');
        return $data;
    }
}
