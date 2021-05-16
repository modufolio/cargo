<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;
use Log;

// SERVICE
use App\Services\FinanceService;

class FinanceController extends BaseController
{
    protected $financeService;

    public function __construct(
        FinanceService $financeService
    )
    {
        $this->financeService = $financeService;
    }

    /**
     * get finance pickup paginate
     *
     * @param Request $request
     */
    public function getFinancePickupPaginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'sort',
            'number',
            'name',
            'receiver',
            'debtor',
            'paymentMethod',
            'createdAt',
            'dateFrom',
            'dateTo',
            'branchId'
        ]);

        try {
            $result = $this->financeService->getFinancePickupService($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
