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
            'customer',
            'pickupOrderNo',
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
            'customer',
            'pickupOrderNo',
            'shipmentPlanNumber',
            'branchId',
            'podNumber',
            'statusDelivery',
            'podStatus'
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

    /**
     * get pending and draft pod
     * only admin
     */
    public function getPendingAndDraft(Request $request)
    {
        try {
            $result = $this->podService->getPendingAndDraftService($request);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * redelivery pod
     */
    public function redeliveryPOD(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'userId',
            'notes'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->podService->redeliveryPODService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * api submit pod dari driver
     */
    public function submitDriver(Request $request)
    {
        $data = $request->only([
            'pickupId', 'userId', 'notes', 'statusDelivery', 'picture'
        ]);
        DB::beginTransaction();
        try {
            $result = $this->podService->submitPODDriver($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * get pickup list in POD by driver
     */
    public function getPickupList(Request $request)
    {
        $data = $request->only([
            'podId', 'userId', 'filter'
        ]);
        try {
            $result = $this->podService->getPickupList($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
