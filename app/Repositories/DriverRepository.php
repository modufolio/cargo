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
     * Get Driver by name
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

        $driver = $this->driver->with(['user', 'user.address'])->sortable();

        if (empty($perPage)) {
            $perPage = 15;
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
        return $driver->fresh();
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
            $driver->active == false;
            $driver->save();
            return $driver->fresh();
        } else {
            throw new InvalidArgumentException('Driver tidak dapat dinonaktifkan, karena sedang bertugas');
        }
    }
}
