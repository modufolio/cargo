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
        $perPage = $data['perPage'];
        $page = $data['page'];

        $name = $data['name'];
        $email = $data['email'];
        $role = $data['role'];

        $sort = $data['sort'];

        $user = $this->user->select('id','name','email','role_id','phone')->whereHas('role',)->with(['role' => function($q) {
            $q->select('id','name','slug');
        }]);

        if (empty($perPage)) {
            $perPage = 10;
        }

        if (!empty($sort['field'])) {
            $order = $sort['order'];
            if ($order == 'ascend') {
                $order = 'asc';
            } else if ($order == 'descend') {
                $order = 'desc';
            } else {
                $order = 'desc';
            }
            switch ($sort['field']) {
                case 'id':
                    $user = $user->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'name':
                    $user = $user->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'created_at':
                    $user = $user->sortable([
                        'created_at' => $order
                    ]);
                    break;
                default:
                    $user = $user->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $user = $user->where('id', 'like', '%'.$id.'%');
        }

        if (!empty($name)) {
            $user = $user->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($email)) {
            $user = $user->where('email', 'ilike', '%'.$email.'%');
        }

        if (!empty($role)) {
            $user = $user->whereHas('role', function($q) use ($role) {
                $q->where('slug', $role);
            });
        }

        $result = $user->paginate($perPage);

        return $result;
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
        $user->phone        = $data['phone'] ?? null;
        $user->save();

        return $user->fresh();
    }

    /**
     * Update User
     *
     * @param $data
     * @return User
     */
    public function update($data)
    {
        $user = $this->user->findOrFail($data['id']);

        $user->name         = $data['name'];
        $user->email        = strtolower($data['email']);
        $user->password     = bcrypt($data['password']);
        $user->username     = $data['username'];
        $user->role_id      = $data['role_id'];
        $user->google_id    = $data['google_id'] ?? null;
        $user->phone        = $data['phone'] ?? null;
        $user->save();

        return $user->fresh();
    }
}
