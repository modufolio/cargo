<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class ShipmentPlan extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'deleted_by'
    ];

    protected $guarded = [];

    public $sortable = [
        'pickups',
        'status',
        'id',
        'created_by',
        'deleted_by',
        'updated_by'
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

    public function pickups()
    {
        return $this->hasMany(Pickup::class, 'shipment_plan_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
