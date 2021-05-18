<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory, Sortable, SoftDeletes;
    public $timestamps = true;
    protected $guarded = [];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'user_id'
    ];
    public $sortable = [
        'id',
        'discount',
        'discount_max',
        'start_at',
        'end_at',
        'min_value',
        'updated_at',
        'user',
        'scope'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pickup()
    {
        return $this->hasOne(Pickup::class);
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
