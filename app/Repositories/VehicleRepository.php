<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Models\Driver;
use Carbon\Carbon;
use InvalidArgumentException;

class VehicleRepository
{
    protected $vehicle;
    protected $driver;

    public function __construct(Vehicle $vehicle, Driver $driver)
    {
        $this->vehicle = $vehicle;
        $this->driver = $driver;
    }

    /**
     * Get vehicle by name
     *
     * @param array $data
     * @return Vehicle
     */
    public function getAvailableVehicleByNameRepo($data)
    {
        $data = $this->vehicle->where('status', 'available')->where('name', 'ilike', '%'.$data['value'].'%')->get();
        return $data;
    }

    /**
     * Get vehicle by number
     *
     * @param array $data
     * @return Vehicle
     */
    public function getAvailableVehicleByNumberRepo($data)
    {
        $data = $this->vehicle->where('status', 'available')->where('license_plate', 'ilike', '%'.$data['value'].'%')->get();
        return $data;
    }

    /**
     * Update vehicle
     *
     * @param int $vehicleId
     * @param int $driverId
     * @return Vehicle
     */
    public function assignDriverRepo($vehicleId, $driverId)
    {
        // update driver status
        $driver = $this->driver->find($driverId);
        if (!$driver->active) {
            throw new InvalidArgumentException('Driver di nonaktifkan, silahkan ganti ke driver lain');
        }
        if ($driver->status == 'on-duty') {
            throw new InvalidArgumentException('Driver sedang bertugas, silahkan ganti ke driver lain');
        }
        if (!$driver) {
            throw new InvalidArgumentException('Driver tidak ditemukan');
        }
        $driver->status = 'on-duty';
        $driver->save();

        // assign kendaraan dan update status kendaraan
        $vehicle = $this->vehicle->find($vehicleId);
        if ($vehicle->status == 'on-duty') {
            throw new InvalidArgumentException('Kendaraan sedang digunakan, silahkan ganti ke kendaraan lain');
        }
        if (!$vehicle) {
            throw new InvalidArgumentException('Kendaraan tidak ditemukan');
        }
        $vehicle->driver_id = $driverId;
        $vehicle->status = 'on-duty';
        $vehicle->save();
        return $vehicle->fresh();
    }
}
