<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;
use Log;

// MODELS
use App\Services\BillService;
use App\Services\RouteService;

class BillController extends BaseController
{
    protected $billService;
    protected $routeService;

    public function __construct(BillService $billService, RouteService $routeService)
    {
        $this->billService = $billService;
        $this->routeService = $routeService;
    }

    /**
     * Calculate Price based on origin and destination
     *
     * @param Request $request
     */
    public function calculatePrice(Request $request)
    {
        $data = $request->only([
            'items',
            'origin',
            'destination',
            'fleetId'
        ]);

        try {
            $route = $this->routeService->getByCityService($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage());
        }

        try {
            $result = $this->billService->calculatePriceWithoutPromo($data['items'], $route);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }
}
