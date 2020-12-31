<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $casts = [
        'ranking' => 'double',
    ];
    public $hidden = ['created_at','updated_at'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class);
    }
}
