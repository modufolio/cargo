<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;

// SERVICE
use App\Services\VehicleService;
class VehicleController extends BaseController
{
    protected $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Searching vehicle by name.
     *
     * @param Request $request
     * @return Vehicle
     */
    public function search(Request $request)
    {
        $data = $request->only([
            'value',
            'type'
        ]);

        try {
            $result = $this->vehicleService->getVehicleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * pagination vehicle.
     *
     * @param Request $request
     * @return Vehicle
     */
    public function paginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'sort',
            'id',
            'licensePlate',
            'name',
            'type',
            'maxVolume',
            'maxWeight',
            'status'
        ]);

        try {
            $result = $this->vehicleService->paginateVehicleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * edit vehicle.
     *
     * @param Request $request
     * @return Vehicle
     */
    public function edit(Request $request)
    {
        $data = $request->only([
            'id',
            'licensePlate',
            'name',
            'type',
            'maxVolume',
            'maxWeight',
            'status',
            'active',
            'affiliate'
        ]);

        try {
            $result = $this->vehicleService->editVehicleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * create vehicle.
     *
     * @param Request $request
     * @return Vehicle
     */
    public function create(Request $request)
    {
        $data = $request->only([
            'licensePlate',
            'name',
            'type',
            'maxVolume',
            'maxWeight',
            'status',
            'affiliate'
        ]);

        try {
            $result = $this->vehicleService->createVehicleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * Delete vehicle
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $data = $request->only([
            'vehicleId'
        ]);
        try {
            $result = $this->vehicleService->deleteVehicleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get 10 vehicle by name.
     *
     * @param Request $request
     * @return Vehicle
     */
    public function getTenVehicle(Request $request)
    {
        try {
            $result = $this->vehicleService->getTenVehicleService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }
}
