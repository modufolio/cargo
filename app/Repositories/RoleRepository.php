<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use InvalidArgumentException;
use Illuminate\Support\Str;

class RoleRepository
{
    protected $role;
    protected $user;
    protected $feature;

    public function __construct(Role $role, User $user, Feature $feature)
    {
        $this->role = $role;
        $this->user = $user;
        $this->feature = $feature;
    }

    /**
     * Get all roles.
     *
     * @return Role $role
     */
    public function getAll()
    {
        return $this->role->get();
    }

    /**
     * Get role by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->role->where('id', $id)->get();
    }

    /**
     * Save Role
     *
     * @param $data
     * @return Role
     */
    public function saveRoleRepo($data)
    {
        $role = new $this->role;

        $role->name = $data['name'];
        $role->ranking = $data['ranking'];
        $slug = Str::of($data['name'])->slug('-');
        $role->slug = $slug;
        $role->description = $data['description'];
        $role->save();
        $role->features()->attach($data['features']);
        return $role->fresh();
    }

    /**
     * Update Role
     *
     * @param $data
     * @return Role
     */
    public function updateRoleRepo($data)
    {
        $role = $this->role->find($data['id']);
        if (!$role) {
            throw new InvalidArgumentException('Maaf, data peran tidak ditemukan');
        }
        $role->name = $data['name'];
        $slug = Str::of($data['name'])->slug('-');
        $role->slug = $slug;
        $role->ranking = $data['ranking'];
        $role->description = $data['description'];
        $role->save();
        $role->features()->sync($data['features'], false);;
        return $role->fresh();
    }

    /**
     * Update Role
     *
     * @param $data
     * @return Role
     */
    public function delete($id)
    {
        $role = $this->role->find($id);
        if (!$role) {
            throw new InvalidArgumentException('Maaf, data peran tidak ditemukan');
        }
        $role->delete();
        return $role;
    }

    /**
     * Check role admin user
     * @param array $data
     */
    public function checkRoleAdminRepo($data = [])
    {
        $role = $this->user->find($data['id'])->role;
        if ($role->slug == 'admin') {
            return $role;
        }
        throw new InvalidArgumentException('Maaf role anda tidak diizinkan');
    }

    /**
     * Check role driver user
     * @param array $data
     */
    public function checkRoleDriverRepo($data = [])
    {
        $role = $this->user->find($data['id'])->role;
        if ($role->slug == 'driver' || $role->slug == 'driver-3pl') {
            return $role;
        }
        throw new InvalidArgumentException('Maaf role anda tidak diizinkan');
    }

    /**
     * Check role customer user
     * @param array $data
     */
    public function checkRoleCustomerRepo($data = [])
    {
        $role = $this->user->find($data['id'])->role;
        if ($role->slug == 'customer') {
            return $role;
        }
        throw new InvalidArgumentException('Maaf role anda tidak diizinkan');
    }

    /**
     * Role Pagination
     *
     * @param array $data
     */

    public function rolePaginationRepo($data = [])
    {
        $perPage = $data['perPage'];
        $sort = $data['sort'];
        $id = $data['id'];
        $name = $data['name'];
        $ranking = $data['ranking'];

        $role = $this->role->with(['features']);

        if (empty($perPage)) {
            $perPage = 15;
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
                    $role = $role->sortable(['id' => $order]);
                    break;
                case 'ranking':
                    $role = $role->sortable(['ranking' => $order]);
                    break;
                case 'name':
                    $role = $role->sortable(['name' => $order]);
                    break;
                default:
                    $role = $role->sortable(['id' => 'desc']);
                    break;
            }
        }

        if (!empty($ranking)) {
            $role = $role->where('ranking', 'ilike', '%'.$ranking.'%');
        }

        if (!empty($name)) {
            $role = $role->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($id)) {
            $role = $role->where('id', 'ilike', '%'.$id.'%');
        }

        $role = $role->paginate($perPage);

        return $role;
    }

    /**
     * Get list feature
     */
    public function featureListRepo()
    {
        $feature = $this->feature->select('name','id','slug')->get();
        return $feature;
    }
}
