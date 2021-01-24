<?php

namespace App\Repositories;

use App\Models\Driver;
use Carbon\Carbon;

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
    public function getDriverByVehicleRepo($data)
    {
        $data = $this->driver->with('user')->whereHas('vehicles', function($q) use ($data) {
            $q->where('id', $data['vehicleId']);
        })->get();
        return $data;
    }
}
