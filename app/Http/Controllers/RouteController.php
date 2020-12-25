<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use DB;
use Exception;

// SERVICE
use App\Services\RouteService;
use App\Services\PickupService;

class RouteController extends BaseController
{
    protected $routeService;

    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    /**
     * Get fleet
     * @param Array $request
     * @param fleet_id fleetId
     * @param origin origin
     * @param destination destination
     */
    public function getByFleetOriginDestination(Request $request)
    {
        $data = $request->only([
            'fleetId',
            'origin',
            'destination',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->routeService->getByFleetOriginDestination($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse($result);
    }
}
