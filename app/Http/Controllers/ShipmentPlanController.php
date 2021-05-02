<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

// SERVICE
use App\Services\ShipmentPlanService;
use App\Services\PickupService;

class ShipmentPlanController extends BaseController
{
    protected $shipmentPlanService;
    protected $pickupService;

    public function __construct(ShipmentPlanService $shipmentPlanService, PickupService $pickupService)
    {
        $this->shipmentPlanService = $shipmentPlanService;
        $this->pickupService = $pickupService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'vehicleId',
            'driverId',
            'userId',
            'isTransit',
            'transitBranch'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanService->saveShipmentPlanService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * Delete pickup plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $data = $request->only([
            'userId',
            'pickupPlanId',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanService->deleteShipmentPlanService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * get pickup order inside shipment plan with pagination
     * only admin
     */
    public function getPaginatePickup(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'name',
            'city',
            'id',
            'district',
            'village',
            'picktime',
            'sort',
            'number',
            'branchId',
            'isTransit'
        ]);
        try {
            $result = $this->pickupService->getReadyToShipmentService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get list shipment plan paginate
     * only admin
     */
    public function getList(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'startDate',
            'endDate',
            'id',
            'status',
            'driver',
            'licenseNumber',
            'vehicleType',
            'sort',
            'branchId'
        ]);
        try {
            $result = $this->pickupService->getListShipmentPlanService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * delete pickup order inside pickup plan
     * only admin
     */
    public function deletePickupOrder(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'shipmentPlanId'
        ]);
        try {
            $result = $this->shipmentPlanService->deletePoService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * create pickup order inside shipment plan
     * only admin
     */
    public function addPickupOrder(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'shipmentPlanId'
        ]);
        try {
            $result = $this->shipmentPlanService->addPoService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get pickup plan paginate
     * only driver
     */
    public function getDriverPickupPlanList(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'startDate',
            'endDate',
            'id',
            'status',
            'licenseNumber',
            'vehicleType',
            'sort',
            'userId'
        ]);
        try {
            $result = $this->pickupService->getDriverPickupPlanListService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get pickup pagination for driver
     * only driver
     */
    public function getPaginatePickupDriver(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'userId',
            'name',
            'city',
            'id',
            'district',
            'village',
            'picktime',
            'sort'
        ]);
        try {
            $result = $this->pickupService->getReadyToPickupDriverService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * Cancel shipment plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $data = $request->only([
            'userId',
            'shipmentPlanId',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanService->cancelShipmentPlanService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * get list shipment plan driver
     */
    public function getDriverShipmentPlanList(Request $request)
    {
        $data = $request->only([
            'userId',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanService->getDriverShipmentPlanListService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * get pickup order in shipment plan driver
     */
    public function getPickupOrderDriverShipmentPlanList(Request $request)
    {
        $data = $request->only([
            'userId',
            'shipmentPlanId'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanService->getPickupOrderDriverShipmentPlanListService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * get dashboard for driver
     */
    public function getDashboardDriver(Request $request)
    {
        $data = $request->only([
            'shipmentPlanId'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanService->getDashboardDriverService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }
}
