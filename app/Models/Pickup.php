<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Kyslik\ColumnSortable\Sortable;
use App\Models\Item;

class Pickup extends Model
{
    // Status : applied, canceled, request
    use HasFactory, SoftDeletes, Sortable;

    public $timestamps = true;

    protected $guarded = [];

    public $sortable = [
        'created_at',
        'sender',
        'name',
        'pickup_plan_id',
        'picktime',
        'id',
        'user'
    ];

    protected $hidden = [
        'user_id',
        'promo_id',
        'fleet_id',
        'sender_id',
        'receiver_id',
        'debtor_id',
        'updated_at',
        'deleted_at',
        'created_by',
        'deleted_by'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(Sender::class);
    }

    public function receiver()
    {
        return $this->belongsTo(Receiver::class);
    }

    public function debtor()
    {
        return $this->belongsTo(Debtor::class);
    }

    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function pickupPlan()
    {
        return $this->belongsTo(PickupPlan::class, 'pickup_plan_id');
    }

    public function proofOfPickup()
    {
        return $this->hasOne(ProofOfPickup::class, 'pickup_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shipmentPlan()
    {
        return $this->belongsTo(ShipmentPlan::class, 'shipment_plan_id');
    }
}
