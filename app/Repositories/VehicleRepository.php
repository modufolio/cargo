<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Carbon\Carbon;

class VehicleRepository
{
    protected $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Get vehicle by name
     *
     * @param array $data
     * @return Vehicle
     */
    public function getVehicleByNameRepo($data)
    {
        $data = $this->vehicle->where('name', 'ilike', '%'.$data['value'].'%')->get();
        return $data;
    }

    /**
     * Get vehicle by number
     *
     * @param array $data
     * @return Vehicle
     */
    public function getVehicleByNumberRepo($data)
    {
        $data = $this->vehicle->where('license_plate', 'ilike', '%'.$data['value'].'%')->get();
        return $data;
    }
}
