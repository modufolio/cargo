<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at'];

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }
}
