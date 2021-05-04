<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class Tracking extends Model
{
    use HasFactory, Sortable;

    public $timestamps = true;

    protected $guarded = [];

    public $sortable = [
        'pickup_id',
        'pickup',
        'id',
    ];

    protected $hidden = [
        'pickup_id'
    ];

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

    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }
}
