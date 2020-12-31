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
     * Get All User
     *
     * @return User
     */
    public function getAll()
    {
        return $this->user->get();
    }

    /**
     * Get All User
     *
     * @return User
     */
    public function getPaginate($data)
    {
        $user = new $this->user;
        $user = $user->paginate($data['per_page']);
        return $user;
    }

    /**
     * Get user by id
     *
     * @param int $id
     * @return User
     */
    public function getById($id)
    {
        return $this->user->findOrFail($id);
    }

    /**
     * Get user by email
     *
     * @param string $email
     * @return User
     */
    public function getByEmail($email)
    {
        return $this->user->where('email', $email)->first();
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

        $user->name         = $data['name'];
        $user->email        = strtolower($data['email']);
        $user->password     = bcrypt($data['password']);
        $user->username     = $data['username'];
        $user->role_id      = $data['role_id'];
        $user->google_id    = $data['google_id'] ?? null;
        $user->save();

        return $user->fresh();
    }
}
