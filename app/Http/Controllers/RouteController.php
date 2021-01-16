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
     * Get route by fleet and origin destination
     * @param Request $request
     * @param int fleetId
     * @param string origin
     * @param string destination
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
        return $this->sendResponse(null, $result);
    }

    /**
     * Get route paginate
     * @param Request $request
     */
    public function paginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'origin',
            'destination',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->routeService->getAllPaginate($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }
}
