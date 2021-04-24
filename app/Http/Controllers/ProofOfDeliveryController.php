<?php

namespace App\Http\Controllers;

// SERVICE
use App\Services\ProofOfDeliveryService;
use App\Services\PickupService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class ProofOfDeliveryController extends BaseController
{
    protected $podService;
    protected $pickupService;

    public function __construct(ProofOfDeliveryService $podService, PickupService $pickupService)
    {
        $this->podService = $podService;
        $this->pickupService = $pickupService;
    }

    /**
     * get list pickup outstanding delivery
     * only admin
     */
    public function getOutstanding(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'sort',
            'general',
            'customer',
            'pickupOrderNo',
            'requestPickupDate',
            'shipmentPlanNumber',
            'branchId'
        ]);
        try {
            $result = $this->podService->getOutstandingService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get list pickup submitted
     * only admin
     */
    public function getSubmitted(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'sort',
            'general',
            'customer',
            'popNumber',
            'popDate',
            'poNumber',
            'popStatus',
            'poStatus',
            'poCreatedDate',
            'poPickupDate',
            'pickupPlanNumber',
            'driverPick',
        ]);
        try {
            $result = $this->podService->getSubmittedService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get detail pickup of pod
     * only admin
     */
    public function getDetailPickup(Request $request)
    {
        $data = $request->only([
            'pickupId',
        ]);
        try {
            $result = $this->podService->getDetailPickupAdmin($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * update status delivery proof of delivery
     */
    public function updateStatusDeliveryPOD(Request $request)
    {
        $data = $request->only([
            'statusDelivery',
            'userId',
            'pickupId',
            'notes'
        ]);
        try {
            $result = $this->podService->updateStatusDeliveryPODService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
