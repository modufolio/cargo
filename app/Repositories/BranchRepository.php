<?php

namespace App\Repositories;

use App\Models\Branch;

class BranchRepository
{
    protected $branch;

    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }

    /**
     * Get All Branch
     *
     * @return Branch
     */
    public function getAll()
    {
        return $this->branch->get();
    }

    /**
     * Get All Branch
     *
     * @return Branch
     */
    public function getPaginate($data)
    {
        $perPage = $data['perPage'];
        $page = $data['page'];

        $name = $data['name'];
        $email = $data['email'];
        $role = $data['role'];

        $sort = $data['sort'];

        $branch = $this->branch->select('id','name','email','role_id','phone')->whereHas('role',)->with(['role' => function($q) {
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
                    $branch = $branch->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'name':
                    $branch = $branch->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'created_at':
                    $branch = $branch->sortable([
                        'created_at' => $order
                    ]);
                    break;
                default:
                    $branch = $branch->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $branch = $branch->where('id', 'like', '%'.$id.'%');
        }

        if (!empty($name)) {
            $branch = $branch->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($email)) {
            $branch = $branch->where('email', 'ilike', '%'.$email.'%');
        }

        if (!empty($role)) {
            $branch = $branch->whereHas('role', function($q) use ($role) {
                $q->where('slug', $role);
            });
        }

        $result = $branch->paginate($perPage);

        return $result;
    }

    /**
     * Get branch by id
     *
     * @param int $id
     * @return Branch
     */
    public function getById($id)
    {
        return $this->branch->findOrFail($id);
    }

    /**
     * Get branch by slug
     *
     * @param string $slug
     * @return Branch
     */
    public function getBySlug($slug)
    {
        return $this->branch->where('slug', $slug)->first();
    }

    /**
     * Save Branch
     *
     * @param $data
     * @return Branch
     */
    public function save($data)
    {
        $branch = new $this->branch;

        $branch->name = $data['name'];
        $branch->slug = strtolower($data['slug']);
        $branch->province = $data['province'];
        $branch->city = $data['city'];
        $branch->district = $data['district'];
        $branch->village = $data['village'];
        $branch->postal_code = $data['postal_code'];
        $branch->street = $data['street'];
        $branch->save();

        return $branch->fresh();
    }

    /**
     * Delete data Branch
     *
     * @param array $data
     * @return Branch
     */
    public function delete($id)
    {
        $branch = $this->branch->findOrFail($id);
        $branch->delete();
        return $branch;
    }
}
