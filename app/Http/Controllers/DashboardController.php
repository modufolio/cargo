<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

use App\Http\Controllers\BaseController;

use App\Services\PickupService;
use App\Services\VehicleService;
use App\Services\UserService;
use App\Services\RouteService;

class DashboardController extends BaseController
{
    protected $pickupService;
    protected $vehicleService;
    protected $userService;
    protected $routeService;

    public function __construct(
        PickupService $pickupService,
        VehicleService $vehicleService,
        UserService $userService,
        RouteService $routeService
    )
    {
        $this->pickupService = $pickupService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->routeService = $routeService;
    }

    /**
     * get card in dashboard.
     * @param Request $request
     */
    public function getCardData(Request $request)
    {
        $data = $request->only([
            'branchId',
        ]);
        // get total order
        try {
            $order = $this->pickupService->getOrderOnBranchService($data['branchId']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        // get total vehicle
        try {
            $vehicle = $this->vehicleService->getTotalVehicleService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        // get total customer
        try {
            $user = $this->userService->getTotalCustomerService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        // get total route
        try {
            $route = $this->routeService->getTotalRouteService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        $result = [
            'order' => $order,
            'vehicle' => $vehicle,
            'user' => $user,
            'route' => $route
        ];
        return $this->sendResponse(null, $result);
    }
}
