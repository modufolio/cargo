<?php

namespace App\Repositories;

use App\Models\Driver;
use Carbon\Carbon;
use InvalidArgumentException;

class DriverRepository
{
    protected $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
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
}
