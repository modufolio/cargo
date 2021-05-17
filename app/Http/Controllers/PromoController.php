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

    /**
     * Display a listing of the resource paginate.
     *
     * @return Branch with paginate
     * @param Request $request
     */
    public function paginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'discount',
            'discountMax',
            'minValue',
            'startAt',
            'endAt',
            'id',
            'sort'
        ]);
        try {
            $result = $this->promoService->getPromoPaginateService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * Create promo.
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        $data = $request->only([
            'userId',
            'code',
            'customerId',
            'description',
            'discount',
            'discountMax',
            'endAt',
            'maxUsed',
            'minValue',
            'startAt',
            'terms'
        ]);
        try {
            $result = $this->promoService->createPromoService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
