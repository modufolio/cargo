<?php

namespace App\Http\Controllers;

// SERVICE
use App\Services\ProofOfPickupService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class ProofOfPickupController extends BaseController
{
    protected $popService;

    public function __construct(ProofOfPickupService $popService)
    {
        $this->popService = $popService;
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
            'driverPick'
        ]);
        try {
            $result = $this->popService->createPOPService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
