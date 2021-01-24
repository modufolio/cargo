<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;

// SERVICE
use App\Services\DriverService;

class DriverController extends BaseController
{
    protected $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    /**
     * Searching driver by vehicle id.
     *
     * @param Request $request
     * @return Driver
     */
    public function searchByVehicle(Request $request)
    {
        $data = $request->only([
            'vehicleId',
        ]);

        try {
            $result = $this->driverService->getByVehicleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }
}
