<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use DB;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RouteExport;
use App\Models\Route;

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
            'destination_city',
            'destination_district'
        ]);
        try {
            $result = $this->routeService->getByFleetOriginDestinationService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
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
            'destinationCity',
            'destinationDistrict',
            'price',
            'minWeight',
            'fleet',
            'sort'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->routeService->getAllPaginateService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * Create new route
     * @param Request $request
     */
    public function create(Request $request)
    {
        $data = $request->only([
            'fleet',
            'origin',
            'destinationIsland',
            'destinationCity',
            'destinationDistrict',
            'price',
            'priceCar',
            'priceMotorcycle',
            'minWeight',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->routeService->createRouteService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * Edit route
     * @param Request $request
     */
    public function edit(Request $request)
    {
        $data = $request->only([
            'fleet',
            'id',
            'origin',
            'destinationIsland',
            'destinationCity',
            'destinationDistrict',
            'price',
            'priceCar',
            'priceMotorcycle',
            'minWeight',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->routeService->editRouteService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * Get destination island
     * @return Route
     */
    public function listIsland()
    {
        try {
            $result = $this->routeService->getDestinationIslandService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * Delete route
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $data = $request->only([
            'routeId'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->routeService->deleteRouteService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * import route
     */
    public function importRoute(Request $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->routeService->importRouteService($request);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * export route
     */
    public function exportRoute(Request $request)
    {
        return new RouteExport();
        return Excel::download(new RouteExport, 'route.xlsx');
    }
}
