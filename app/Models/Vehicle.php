<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
        'driver_id',
    ];

    public $sortable = [
        'id',
        'type',
        'name',
        'status',
        'max_weight',
        'max_volume',
        'license_plate',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function pickupPlans()
    {
        return $this->hasMany(PickupPlan::class);
    }
}
