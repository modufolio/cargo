<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

use App\Models\Feature;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $timestamps = true;
    // protected $appends = ['feature','role'];

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function getFeaturesAttribute()
    {
        $role = Role::find($this->attributes['role_id'])->features()->get();
        foreach ($role as $value) {
            $val = [
                'id' => $value->id,
                'name' => $value->name,
                'slug' => $value->slug,
            ];
            $data[] = $val;
        }
        return $data;
    }

    public function getRoleAttribute()
    {
        $data = Role::find($this->attributes['role_id']);
        return $data;
    }
}
