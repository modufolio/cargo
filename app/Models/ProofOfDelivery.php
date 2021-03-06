<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// VENDOR
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class ProofOfDelivery extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    public $timestamps = true;

    protected $guarded = [];

    public $sortable = [
        'created_at',
        'updated_at',
        'pickup',
        'shipment_plan',
        'id',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_by',
        'deleted_by',
        'updated_by',
        'pickup_id'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
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
