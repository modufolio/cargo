<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

// SERVICE
use App\Services\TrackingService;

class TrackingController extends BaseController
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * get tracking of pickup id.
     */
    public function index(Request $request)
    {
        $data = $request->only([
            'pickupId',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->trackingService->getTrackingByPickupService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * save tracking data.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'pickupId',
            'docs',
            'status',
            'notes',
            'picture',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->trackingService->recordTrackingByPickupService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }

    /**
     * record picture for tracking data.
     */
    public function uploadPicture(Request $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->trackingService->uploadTrackingPictureService($request);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
    }
}
