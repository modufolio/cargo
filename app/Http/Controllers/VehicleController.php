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
}
