<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository
{
    protected $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
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
    public function save($data)
    {
        $role = new $this->role;

        $role->name = $data['name'];
        $role->slug = $data['slug'];
        $role->ranking = $data['ranking'];
        $role->features = $data['features'];
        $role->save();

        return $role->fresh();
    }

    /**
     * Update Role
     *
     * @param $data
     * @return Role
     */
    public function update($data, $id)
    {
        $role = $this->role->find($id);

        $role->name = $data['name'];
        $role->slug = $data['slug'];
        $role->ranking = $data['ranking'];
        $role->features = $data['features'];
        $role->save();

        return $role;
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
        $role->delete();
        return $role;
    }
}
