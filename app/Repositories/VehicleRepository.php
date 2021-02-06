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

    /**
     * Vehicle Pagination
     *
     * @param array $data
     */

    public function vehiclePaginationRepo($data = [])
    {
        $perPage = $data['perPage'];
        $sort = $data['sort'];
        $licensePlate = $data['licensePlate'];
        $id = $data['id'];
        $name = $data['name'];
        $type = $data['type'];
        $maxVolume = $data['maxVolume'];
        $maxWeight = $data['maxWeight'];
        $status = $data['status'];

        $vehicle = $this->vehicle;

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
                    $vehicle = $vehicle->sortable(['id' => $order]);
                    break;
                case 'license_plate':
                    $vehicle = $vehicle->sortable(['license_plate' => $order]);
                    break;
                case 'name':
                    $vehicle = $vehicle->sortable(['name' => $order]);
                    break;
                case 'type':
                    $vehicle = $vehicle->sortable(['type' => $order]);
                    break;
                case 'max_volume':
                    $vehicle = $vehicle->sortable(['max_volume' => $order]);
                    break;
                case 'max_weight':
                    $vehicle = $vehicle->sortable(['max_weight' => $order]);
                    break;
                case 'status':
                    $vehicle = $vehicle->sortable(['status' => $order]);
                    break;
                default:
                    $vehicle = $vehicle->sortable(['id' => 'desc']);
                    break;
            }
        }

        if (!empty($licensePlate)) {
            $vehicle = $vehicle->where('license_plate', 'ilike', '%'.$licensePlate.'%');
        }

        if (!empty($name)) {
            $vehicle = $vehicle->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($type)) {
            $vehicle = $vehicle->where('type', 'ilike', '%'.$type.'%');
        }

        if (!empty($maxVolume)) {
            $vehicle = $vehicle->where('max_volume', 'like', '%'.$maxVolume.'%');
        }

        if (!empty($maxWeight)) {
            $vehicle = $vehicle->where('max_weight', 'like', '%'.$maxWeight.'%');
        }

        if (!empty($status)) {
            $vehicle = $vehicle->where('status', 'ilike', '%'.$status.'%');
        }

        $vehicle = $vehicle->paginate($perPage);

        return $vehicle;
    }

    /**
     * Update vehicle
     */
    public function editVehicleRepo($data = [])
    {
        $vehicle = $this->vehicle->find($data['id']);
        if (!$vehicle) {
            throw new InvalidArgumentException('Kendaraan tidak ditemukan');
        }
        $vehicle->license_plate = $data['licensePlate'];
        $vehicle->name = $data['name'];
        $vehicle->type = $data['type'];
        $vehicle->status = $data['status'];
        $vehicle->max_volume = $data['maxVolume'];
        $vehicle->max_weight = $data['maxWeight'];
        $vehicle->save();
        return $vehicle;
    }

    /**
     * Create vehicle
     */
    public function createVehicleRepo($data = [])
    {
        $vehicle = new $this->vehicle;
        $vehicle->driver_id = 1;
        $vehicle->license_plate = $data['licensePlate'];
        $vehicle->name = $data['name'];
        $vehicle->type = $data['type'];
        $vehicle->status = $data['status'];
        $vehicle->max_volume = $data['maxVolume'];
        $vehicle->max_weight = $data['maxWeight'];
        $vehicle->save();
        return $vehicle;
    }
}
