<?php

namespace App\Http\Controllers;

// SERVICE
use App\Services\TransitService;
use App\Services\PickupService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class TransitController extends BaseController
{
    protected $transitService;
    protected $pickupService;

    public function __construct(TransitService $transitService, PickupService $pickupService)
    {
        $this->transitService = $transitService;
        $this->pickupService = $pickupService;
    }

    /**
     * draft transit pickup
     */
    public function draftTransit(Request $request)
    {
        $data = $request->only([
            'received',
            'notes',
            'status',
            'userId',
            'transitId',
            'pickupId'
        ]);
        try {
            $result = $this->transitService->draftTransitService($data);
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
            'transitNumber',
            'pickupOrderNo',
            'branchId'
        ]);
        try {
            $result = $this->transitService->getOutstandingService($data);
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
            'transitNumber',
            'pickupOrderNo',
            'branchId'
        ]);
        try {
            $result = $this->transitService->getSubmittedService($data);
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
    public function getPendingAndDraft(Request $request)
    {
        $data = $request->only([
            'branchId'
        ]);
        try {
            $result = $this->transitService->getPendingAndDraftService($data['branchId']);
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
     * update transit
     */
    public function updateTransit(Request $request)
    {
        $data = $request->only([
            'transitId',
            'status',
            'userId'
        ]);
        try {
            $result = $this->transitService->updateTransitService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
