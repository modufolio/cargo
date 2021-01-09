<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class Route extends Model
{
    use HasFactory, Sortable;
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'price' => 'float',
        'minimum_weight' => 'float'
    ];
    public $sortable = [
        'fleet_id',
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
}
