<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Item extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $guarded = [];

    /**
     * Get the pickup that owns the item.
     */
    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }

    // /**
    //  * Get the unit of item
    //  */
    // public function unit()
    // {
    //     return $this->belongsTo(Unit::class);
    // }

    /**
     * Get the service of item
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
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
