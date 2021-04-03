<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

// SERVICE
use App\Services\PickupPlanService;
use App\Services\PickupService;

class PickupPlanController extends BaseController
{
    protected $pickupPlanService;
    protected $pickupService;

    public function __construct(PickupPlanService $pickupPlanService, PickupService $pickupService)
    {
        $this->pickupPlanService = $pickupPlanService;
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
            'branchId',
            'userId'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->pickupPlanService->savePickupPlanService($data);
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
            $result = $this->pickupPlanService->deletePickupPlanService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * get pickup order inside pickup plan pagination
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
            'sort'
        ]);
        try {
            $result = $this->pickupService->getReadyToPickupService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get list pickup plan paginate
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
            'sort'
        ]);
        try {
            $result = $this->pickupService->getListPickupPlanService($data);
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
            'pickupPlanId'
        ]);
        try {
            $result = $this->pickupPlanService->deletePoService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * create pickup order inside pickup plan
     * only admin
     */
    public function addPickupOrder(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'pickupPlanId'
        ]);
        try {
            $result = $this->pickupPlanService->addPoService($data);
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
     * Cancel pickup plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $data = $request->only([
            'userId',
            'pickupPlanId',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->pickupPlanService->cancelPickupPlanService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }
}
