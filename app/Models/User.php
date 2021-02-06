<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Mail\Mailable;
use Laravel\Passport\HasApiTokens;
use Kyslik\ColumnSortable\Sortable;

use App\Models\Feature;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, Sortable;

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
        'created_at',
        'updated_at',
        'deleted_at',
        'role_id',
        'google_id',
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

    public $sortable = [
        'created_at',
        'name',
        'id',
        'role',
        'branch',
        'address'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
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

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function verifyUser()
    {
        return $this->hasOne(VerifyUser::class);
    }

    public function promoOwnerships()
    {
        return $this->hasMany(Promo::class, 'created_by');
    }

    public function promos()
    {
        return $this->hasMany(Promo::class);
    }

    public function senders()
    {
        return $this->hasMany(Sender::class);
    }

    public function receivers()
    {
        return $this->hasMany(Receiver::class);
    }

    public function debtors()
    {
        return $this->hasMany(Debtor::class);
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creatorPickups()
    {
        return $this->hasMany(Pickup::class, 'created_by');
    }

    public function deletorPickups()
    {
        return $this->hasMany(Pickup::class, 'deleted_by');
    }
}
