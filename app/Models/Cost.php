<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Kyslik\ColumnSortable\Sortable;
use App\Models\ExtraCost;

class Cost extends Model
{
    public $timestamps = true;

    use HasFactory, Sortable;

    protected $guarded = [];

    protected $hidden = [
        'pickup_id'
    ];

    public $sortable = [
        'created_at',
        'updated_at',
        'id',
        'pickup',
        'pickup_id',
        'amount'
    ];

    protected $appends = ['total_extra_cost','margin'];


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

    public function pickup()
    {
        return $this->belongsTo(Pickup::class, 'pickup_id');
    }

    public function extraCosts()
    {
        return $this->hasMany(ExtraCost::class);
    }

    public function getTotalExtraCostAttribute()
    {
        $extraCosts = ExtraCost::where('cost_id', $this->id)->get()->all();
        if (count($extraCosts) > 0) {
            $total = array_sum(array_column($extraCosts, 'amount'));
        } else {
            $total = 0;
        }
        return intval($total);
    }

    public function getMarginAttribute()
    {
        $margin = intval($this->amount) - intval($this->total_extra_cost);
        return intval($margin);
    }
}
