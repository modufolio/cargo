<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $hidden = ['created_at','updated_at'];

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }
}
