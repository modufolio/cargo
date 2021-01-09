<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Kyslik\ColumnSortable\Sortable;

class Pickup extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    public $timestamps = true;

    protected $guarded = [];

    public $sortable = [
        'created_at',
        'sender',
        'picktime'
    ];

    protected $hidden = [
        'user_id',
        'promo_id',
        'fleet_id',
        'sender_id',
        'receiver_id',
        'debtor_id',
        'updated_at',
        'deleted_at'
    ];

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
}
