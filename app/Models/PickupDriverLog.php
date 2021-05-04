<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupDriverLog extends Model
{
    use HasFactory;

    protected $table = 'pickup_driver_logs';

    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function branchTo()
    {
        return $this->belongsTo(Branch::class, 'branch_to');
    }

    public function branchFrom()
    {
        return $this->belongsTo(Branch::class, 'branch_from');
    }
}
