<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;
class Route extends Model
{
    use HasFactory, Sortable;
    protected $hidden = ['updated_at', 'deleted_at'];
    protected $casts = [
        'price' => 'float',
        'minimum_weight' => 'float'
    ];
    public $sortable = [
        'fleet',
        'origin',
        'destination_district',
        'destination_city',
        'price',
        'minimum_weight',
        'created_at'
    ];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }

    public function getCreatedAtAttribute($value)
    {
        // $data = Carbon::parse($value)->setTimezone('Asia/Jakarta')->format('Y-m-d h:m:s');
        // if ($value !== null) {
        //     $data = Carbon::createFromFormat('Y-m-d h:m:s', $value, 'Asia/Jakarta');
        //     return $data;
        // } else {
            $data = Carbon::parse($value)->format('Y-m-d h:m:s');
            return $data;
        // }
    }

    public function getUpdatedAtAttribute($value)
    {
        $data = Carbon::parse($value)->format('Y-m-d h:m:s');
        return $data;
    }
}
