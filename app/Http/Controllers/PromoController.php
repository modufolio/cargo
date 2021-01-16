<?php

namespace App\Http\Controllers;


// SERVICE
use App\Services\PromoService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class PromoController extends BaseController
{
    protected $promoService;

    public function __construct(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function getPromoUser(Request $request)
    {
        $data = $request->only([
            'userId',
        ]);
        try {
            $result = $this->promoService->getPromoUser($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    public function getCreatorPromo(Request $request)
    {
        $data = $request->only([
            'userId',
        ]);

        try {
            $result = $this->promoService->getPromoCreator($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    public function selectPromo(Request $request)
    {
        $data = $request->only([
            'userId',
            'promoId',
            'value'
        ]);
        try {
            $result = $this->promoService->selectPromo($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
