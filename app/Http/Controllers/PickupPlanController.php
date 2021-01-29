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
}
