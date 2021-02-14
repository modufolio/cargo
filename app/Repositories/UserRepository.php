<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;

use InvalidArgumentException;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

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
        $sort = $data['sort'];

        $name = $data['name'];
        $email = $data['email'];
        $role = $data['role'];


        $user = $this->user->select('id','name','email','role_id','phone','username','branch_id')->with(['role' => function($q) {
            $q->select('id','name','slug');
        }, 'branch' => function($q) {
            $q->select('id','name');
        }, 'address']);

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
        $user = $this->user->find($id);
        if (!$user) {
            throw new InvalidArgumentException('pengguna tidak ditemukan');
        }
        return $user;
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
        $user->branch_id    = $data['branch_id'] ?? null;
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
    public function updateUserRepo($data)
    {
        $user = $this->user->find($data['id']);
        if (!$user) {
            throw new InvalidArgumentException('pengguna tidak ditemukan');
        }
        $user->name         = $data['name'];
        $user->username     = $data['username'];
        $user->phone        = $data['phone'] ?? null;
        $user->branch_id    = $data['branch'];
        $user->role_id      = $data['role'];

        $user->save();

        return $user->fresh();
    }

    public function updateBranchRepo($data)
    {
        $user = $this->user->find($data['userId']);
        if (!$user) {
            throw new InvalidArgumentException('pengguna tidak ditemukan');
        }
        $user->branch_id = $data['branchId'];
        $user->save();
        return $user->fresh();
    }

    /**
     * Update data driver
     *
     * @param $data
     * @return User
     */
    public function updateUserOfDriver($data)
    {
        $user = $this->user->where('email', $data['email']);
        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'branch_id' => $data['branchId'],
        ]);
        $user = $user->first();
        return $user;
    }

    /**
     * search user by name
     *
     * @param string $name
     * @return User
     */
    public function getByNameRepo($name)
    {
        return $this->user->select('id','name')->whereHas('role', function($q) {
            $q->where('slug', 'customer');
        })->where('name', 'ilike', '%'.$name.'%')->get();
    }

    /**
     * search user by email
     *
     * @param string $email
     * @return User
     */
    public function getByEmailRepo($email)
    {
        return $this->user->select('id','email')->whereHas('role', function($q) {
            $q->where('slug', 'customer');
        })->where('email', 'ilike', '%'.$email.'%')->get();
    }

    /**
     * Delete data user
     *
     * @param array $data
     * @return User
     */
    public function delete($id)
    {
        $user = $this->user->find($id);
        if (!$user) {
            throw new InvalidArgumentException('pengguna tidak ditemukan');
        }
        $user->delete();
        return $user;
    }

    /**
     * Change Password repo
     *
     * @param array $data
     *
     */
    public function changePasswordRepo($data)
    {
        $user = $this->user->find($data['userId']);
        if (!$user) {
            throw new InvalidArgumentException('Pengguna tidak ditemukan');
        }
        $user->password = bcrypt($data['password']);
        $user->save();
        return $user;
    }

    /**
     * Forgot password
     */
    public function forgotPasswordRepo($data)
    {
        $useEmail = filter_var(strtolower($data['username']), FILTER_VALIDATE_EMAIL);

        if (!$useEmail) {
            $user = $this->user->where('username', strtolower($data['username']))->first();
            if (!$user) {
                throw new InvalidArgumentException('Username tersebut tidak cocok dengan data kami');
            }
        } else {
            $user = $this->user->where('email', strtolower($data['username']))->first();
            if (!$user) {
                throw new InvalidArgumentException('Email tersebut tidak cocok dengan data kami');
            }
        }

        // $email = $user->email;
        $user = $this->user->find($user->id);
        $random = Str::random(8);
        $user->password = bcrypt($random);
        $user->save();
        $result = [
            'user' => $user->fresh(),
            'newPass' => $random
        ];
        return $result;
    }

    /**
     * Update User Profile Mobile
     *
     * @param array $data
     * @return User
     */
    public function updateUserProfileRepo($data)
    {
        $user = $this->user->find($data['userId']);
        if (!$user) {
            throw new InvalidArgumentException('pengguna tidak ditemukan');
        }
        $user->name         = $data['name'];
        $user->username     = $data['username'];
        $user->phone        = $data['phone'] ?? null;
        $user->avatar       = $data['avatar'];
        $user->save();
        return $user;
    }

    /**
     * Upload Avatar
     *
     * @param Request $request
     * @return array
     */
    public function uploadAvatar($request)
    {
        $avatar              = $request->file('avatar');
        $avatar_extension    = $avatar->getClientOriginalExtension();
        $timestamp = Carbon::now('Asia/Jakarta')->timestamp;
        Storage::disk('storage_profile')->put('avatar'.$timestamp.'.'.$avatar_extension,  File::get($avatar));
        $avatar_url              = '/upload/webchat/avatar'.$timestamp.'.'.$avatar_extension;
        return [
            'base_url' => env('APP_URL'),
            'path' => $avatar_url
        ];
    }
}
