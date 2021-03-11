<?php

namespace App\Http\Controllers;

// SERVICE
use App\Services\ProofOfPickupService;
use App\Services\PickupService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class ProofOfPickupController extends BaseController
{
    protected $popService;
    protected $pickupService;

    public function __construct(ProofOfPickupService $popService, PickupService $pickupService)
    {
        $this->popService = $popService;
        $this->pickupService = $pickupService;
    }

    /**
     * create proof of pickup
     */
    public function createPOP(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'status',
            'notes',
            'userId',
            'driverPick',
            'popStatus'
        ]);
        try {
            $result = $this->popService->createPOPService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get list pickup outstanding
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
            'pickupPlanNo',
        ]);
        try {
            $result = $this->popService->getOutstandingService($data);
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
            $result = $this->popService->getSubmittedService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get pending and draft pickup
     * only admin
     */
    public function getPendingAndDraft()
    {
        try {
            $result = $this->popService->getPendingAndDraftService();
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get detail pickup
     * only admin
     */
    public function getDetailPickup(Request $request)
    {
        $data = $request->only([
            'pickupId',
        ]);
        try {
            $result = $this->pickupService->getDetailPickupAdmin($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * update pickup
     */
    public function updatePickup(Request $request)
    {
        $data = $request->only([
            'pickup',
        ]);
        try {
            $result = $this->pickupService->updatePickupService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
