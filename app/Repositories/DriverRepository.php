<?php

namespace App\Repositories;

use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

class DriverRepository
{
    protected $driver;
    protected $user;

    public function __construct(Driver $driver, User $user)
    {
        $this->driver = $driver;
        $this->user = $user;
    }

    /**
     * Get Driver by vehicle
     *
     * @param array $data
     * @return Driver
     */
    public function getAvailableDriverByVehicleRepo($data)
    {
        $data = $this->driver->with('user')->where('status', 'available')->whereHas('vehicles', function($q) use ($data) {
            $q->where('id', $data);
        })->get();
        return $data;
    }

    /**
     * Get Driver by id
     *
     * @param int $data
     * @return Driver
     */
    public function getDriverById($data)
    {
        $data = $this->driver->find($data);
        if (!$data) {
            throw new InvalidArgumentException('Driver tidak ditemukan');
        }
        return $data;
    }

    /**
     * Get available driver by name
     *
     * @param string $data
     * @return Driver
     */
    public function getAvailableDriverByNameRepo($data)
    {
        $data = $this->driver->with('user')->where('status', 'available')->whereHas('user', function($q) use ($data) {
            $q->where('name', 'ilike', '%'.$data.'%');
        })->get();
        return $data;
    }

    /**
     * Get all driver paginate
     *
     * @param $pickupId
     * @return mixed
     */
    public function getAllPaginateRepo($data = [])
    {
        $perPage = $data['perPage'];
        $sort = $data['sort'];
        $page = $data['page'];
        $id = $data['id'];
        $active = $data['active'];
        $status = $data['status'];
        $type = $data['type'];
        $name = $data['name'];
        $email = $data['email'];
        $branch = $data['branch'];
        $phone = $data['phone'];

        $driver = $this->driver->with(['user', 'user.address', 'user.branch']);

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
                    $driver = $driver->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'active':
                    $driver = $driver->sortable([
                        'active' => $order
                    ]);
                    break;
                case 'type':
                    $driver = $driver->sortable([
                        'type' => $order
                    ]);
                    break;
                case 'status':
                    $driver = $driver->sortable([
                        'status' => $order
                    ]);
                    break;
                case 'user.name':
                    $driver = $driver->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'user.phone':
                    $driver = $driver->sortable([
                        'user.phone' => $order
                    ]);
                    break;
                default:
                    $driver = $driver->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $driver = $driver->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($type)) {
            $driver = $driver->where('type', 'ilike', '%'.$type.'%');
        }

        if (!empty($active)) {
            $driver = $driver->where('active', 'ilike', '%'.$active.'%');
        }

        if (!empty($status)) {
            $driver = $driver->where('status', 'ilike', '%'.$status.'%');
        }

        if (!empty($name)) {
            $driver = $driver->whereHas('user', function($q) use ($name) {
                $q->where('name', 'ilike', '%'.$name.'%');
            });
        }

        if (!empty($email)) {
            $driver = $driver->whereHas('user', function($q) use ($email) {
                $q->where('email', 'ilike', '%'.$email.'%');
            });
        }

        if (!empty($phone)) {
            $driver = $driver->whereHas('user', function($q) use ($phone) {
                $q->where('phone', 'ilike', '%'.$phone.'%');
            });
        }

        if (!empty($branch)) {
            $driver = $driver->whereHas('user', function($q) use ($branch) {
                $q->whereHas('branch', function($x) use ($branch) {
                    $x->where('name', 'ilike', '%'.$branch.'%');
                });
            });
        }

        $driver = $driver->paginate($perPage);

        // $driver = $this->driver->sortable(['created_at' => 'desc'])->simplePaginate($perPage);
        return $driver;
    }

    /**
     * edit driver
     *
     * @param array $data
     * @return mixed
     */
    public function editDriverRepo($data = [])
    {
        $driver = $this->driver->find($data['id']);
        if (!$driver) {
            throw new InvalidArgumentException('Driver tidak ditemukan');
        }
        $driver->active = $data['active'];
        $driver->type = $data['type'];
        $driver->save();
        return $driver;
    }

    public function createDriverRepo($data = [], $userId)
    {
        $driver = new $this->driver;
        $driver->type = $data['type'];
        $driver->status = 'available';
        $driver->user_id = $userId;
        $driver->save();
        return $driver;
    }

    public function disableDriverRepo($data = [])
    {
        $driver = $this->driver->find($data['driverId']);
        if (!$driver) {
            throw new InvalidArgumentException('Driver tidak ditemukan');
        }
        if ($driver->status == 'available') {
            $driver->active = false;
            $driver->save();
            return $driver->fresh();
        } else {
            throw new InvalidArgumentException('Driver tidak dapat dinonaktifkan, karena sedang bertugas');
        }
    }

    /**
     * Get All Driver by name
     *
     * @param string $data
     * @return Driver
     */
    public function getAllDriverByNameRepo($data)
    {
        $data = $this->driver->with('user')->whereHas('user', function($q) use ($data) {
            $q->where('name', 'ilike', '%'.$data.'%');
        })->get();
        return $data;
    }

    /**
     * get default driver list
     */
    public function getDefaultDriversRepo()
    {
        $data = $this->driver->with('user')->get()->take(10);
        return $data;
    }
}
