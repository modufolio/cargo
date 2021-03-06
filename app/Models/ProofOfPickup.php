<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProofOfPickup extends Model
{
    // Status : applied, canceled, draft, request (pending)
    // Status pick: success, updated, failed, repickup
    use HasFactory, SoftDeletes, Sortable;

    public $timestamps = true;

    protected $table = 'proof_of_pickups';

    protected $guarded = [];

    public $sortable = [
        'created_at',
        'updated_at',
        'pickup',
        'id',
        'created_by',
        'updated_by',
        'number'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_by',
        'deleted_by',
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
