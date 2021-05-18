<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;
use App\Models\Pickup;

class ShipmentPlan extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'deleted_by'
    ];

    protected $table = 'shipment_plans';

    protected $guarded = [];

    protected $appends = ['total_pickup_order'];

    public $sortable = [
        'pickups',
        'status',
        'id',
        'vehicle',
        'created_by',
        'deleted_by',
        'updated_by',
        'created_at',
        'updated_at'
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

    public function getTotalPickupOrderAttribute()
    {
        $pickups = Pickup::where('shipment_plan_id', $this->id)->get();
        $count = count($pickups);
        return $count;
    }
}
