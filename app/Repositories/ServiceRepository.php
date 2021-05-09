<?php

namespace App\Repositories;

use App\Models\Service;
use Carbon\Carbon;

class ServiceRepository
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get all Service
     *
     * @return Service
     */
    public function getAll()
    {
        return $this->service->get();
    }

    /**
     * get paginate service
     */
    public function getPaginateRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $name = $data['name'];
        $price = $data['price'];
        $sort = $data['sort'];

        $result = $this->service;

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
                    $result = $result->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'name':
                    $result = $result->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'price':
                    $result = $result->sortable([
                        'price' => $order
                    ]);
                    break;
                default:
                    $result = $result->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $result = $result->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $result = $result->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($price)) {
            $result = $result->where('price', 'ilike', '%'.$price.'%');
        }

        $result = $result->paginate($perPage);

        return $result;
    }

    /**
     * create service
     */
    public function createServiceRepo($data = [])
    {
        $service = new $this->service;
        $service->name = $data['name'];
        $service->price = $data['price'];
        $service->save();
        return $service;
    }

    /**
     * update service
     */
    public function updateServiceRepo($data = [])
    {
        $service = $this->service->find($data['id']);
        $service->name = $data['name'];
        $service->price = $data['price'];
        $service->save();
        return $service;
    }
}
