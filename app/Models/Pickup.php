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
    use HasFactory, SoftDeletes, Sortable;

    public $timestamps = true;

    protected $guarded = [];

    public $sortable = [
        'created_at',
        'sender',
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

    // protected $appends = ['total_volume'];

    /**
     * Get the items for the pickup data.
     */
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

    // public function getTotalVolumeAttribute()
    // {
    //     $pickups = Pickup::where('pickup_plan_id', $this->attributes['pickup_plan_id'])->get();
    //     $totalVolumeItem = 0;
    //     foreach ($pickups as $key => $value) {
    //         $items = Item::where('unit_id', 3)->where('pickup_id', $value['id'])->get();
    //         $sum = 0;
    //         foreach ($items as $k => $val) {
    //             if(isset($val['unit_total'])) {
    //                 $sum += intval($val['unit_total']);
    //             }
    //         }
    //         $totalVolumeItem += $sum;
    //     }
    //     return $totalVolumeItem;
    // }
}
