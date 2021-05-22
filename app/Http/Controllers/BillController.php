<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;
use Log;

// MODELS
use App\Services\BillService;
use App\Services\RouteService;
use App\Services\PromoService;

class BillController extends BaseController
{
    protected $billService;
    protected $routeService;
    protected $promoService;

    public function __construct(
        BillService $billService,
        RouteService $routeService,
        PromoService $promoService
    )
    {
        $this->billService = $billService;
        $this->routeService = $routeService;
        $this->promoService = $promoService;
    }

    /**
     * Calculate Price based on origin and destination
     *
     * @param Request $request
     */
    public function calculatePrice(Request $request)
    {
        $data = $request->only([
            'items',
            'origin',
            'destination',
            'fleetId',
            'promoId'
        ]);

        try {
            $route = $this->routeService->getByCityService($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage());
        }

        if (empty($data['promoId'])) {
            try {
                $result = $this->billService->calculatePriceWithoutPromo($data['items'], $route);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                return $this->sendError($e->getMessage());
            }
        } else {
            try {
                $promo = $this->promoService->getPromoByIdService($data['promoId']);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                return $this->sendError($e->getMessage());
            }

            try {
                $result = $this->billService->calculatePriceService($data['items'], $route, $promo);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                return $this->sendError($e->getMessage());
            }
        }


        return $this->sendResponse(null, $result);
    }
}
