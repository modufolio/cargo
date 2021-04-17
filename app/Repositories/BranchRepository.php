<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Support\Str;
use InvalidArgumentException;

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
    public function getAllBranchRepo()
    {
        return $this->branch->select('name', 'id')->get();
    }

    /**
     * Get all branch paginate
     *
     * @param array $data
     * @return mixed
     */
    public function getAllPaginateRepo($data = [])
    {
        $sort = $data['sort'];
        $perPage = $data['perPage'];

        $name = $data['name'];
        $id = $data['id'];
        $province = $data['province'];
        $city = $data['city'];
        $district = $data['district'];

        $branch = $this->branch;

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
                case 'name':
                    $branch = $branch->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'province':
                    $branch = $branch->sortable([
                        'province' => $order
                    ]);
                    break;
                case 'city':
                    $branch = $branch->sortable([
                        'city' => $order
                    ]);
                    break;
                case 'district':
                    $branch = $branch->sortable([
                        'district' => $order
                    ]);
                    break;
                case 'id':
                    $branch = $branch->sortable([
                        'id' => $order
                    ]);
                    break;
                default:
                    $branch = $branch->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($name)) {
            $branch = $branch->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($id)) {
            $branch = $branch->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($province)) {
            $branch = $branch->where('province', 'ilike', '%'.$province.'%');
        }

        if (!empty($district)) {
            $branch = $branch->where('district', 'ilike', '%'.$district.'%');
        }

        if (!empty($city)) {
            $branch = $branch->where('city', 'like', '%'.$city.'%');
        }

        $branch = $branch->paginate($perPage);

        return $branch;
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
    public function saveBranchRepo($data = [])
    {
        $branch = new $this->branch;

        $branch->name = $data['name'];
        $slug = Str::of($data['name'])->slug('-');
        $branch->slug = $slug;
        $branch->province = $data['province'];
        $branch->city = $data['city'];
        $branch->district = $data['district'];
        $branch->village = $data['village'];
        $branch->postal_code = $data['postalCode'];
        $branch->street = $data['street'];
        $branch->save();

        return $branch;
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

    /**
     * edit Branch
     *
     * @param $data
     * @return Branch
     */
    public function updateBranchRepo($data = [])
    {
        $branch = $this->branch->find($data['id']);

        if (!$branch) {
            throw new InvalidArgumentException('Cabang tidak ditemukan');
        }

        $branch->name = $data['name'];
        $slug = Str::slug($data['name'], '-');
        $branch->slug = $slug;
        $branch->province = $data['province'];
        $branch->city = $data['city'];
        $branch->district = $data['district'];
        $branch->village = $data['village'];
        $branch->postal_code = $data['postalCode'];
        $branch->street = $data['street'];
        $branch->save();

        return $branch;
    }

    /**
     * Get default Branchs list
     *
     * @return Branch
     */
    public function getDefaultBranchRepo()
    {
        return $this->branch->select('name', 'id')->get()->take(10);
    }

    /**
     * check branch by pickup
     *
     * @param $pickupId
     * @return Branch
     */
    public function checkBranchByPickupRepo($pickupId)
    {
        $branch = $this->branch->whereHas('pickups', function($q) use ($pickupId) {
            $q->where('id', $pickupId);
        })->first();
        return $branch;
    }
}
