<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;

class UserRepository
{
    protected $user;
    protected $role;

    public function __construct(Role $role, User $user)
    {
        $this->role = $role;
        $this->user = $user;
    }

    /**
     * Save User
     *
     * @param $data
     * @return User
     */
    public function save($data)
    {
        $user = new $this->user;

        $user->name     = $data['name'];
        $user->email    = strtolower($data['email']);
        $user->password = bcrypt($data['password']);
        $user->username = $data['username'];
        $user->role_id  = $data['role_id'];
        $user->save();

        return $user->fresh();
    }
}
