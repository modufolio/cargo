<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Kyslik\ColumnSortable\Sortable;

class Cost extends Model
{
    public $timestamps = true;

    use HasFactory, Sortable;

    public $hidden = [
        'pickup_id'
    ];

    public $sortable = [
        'created_at',
        'updated_at',
        'id',
        'pickup',
        'pickup_id',
        'amount'
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
        return $this->belongsTo(Pickup::class, 'pickup_id');
    }
}
