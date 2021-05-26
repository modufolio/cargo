<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use DB;
use Exception;

// SERVICE
// use App\Services\RouteService;
// use App\Services\PickupService;
use App\Services\ReportService;

class ReportController extends BaseController
{
    // protected $routeService;
    // protected $pickupService;
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        // $this->routeService = $routeService;
        $this->reportService = $reportService;
    }

    /**
     * @param Request $request
     */
    public function getReportPickupWithRange(Request $request)
    {
        $data = $request->only([
            'startDate',
            'endDate',
            'status'
        ]);
        try {
            $result = $this->reportService->getReportPickupWithRangeService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * @param Request $request
     */
    public function getReportSuccessPickupWithRange(Request $request)
    {
        $data = $request->only([
            'startDate',
            'endDate'
        ]);
        try {
            $result = $this->reportService->getReportSuccessPickupWithRangeService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get reporting
     */
    public function getReport(Request $request)
    {
        $data = $request->only([
            'startDate',
            'endDate',
            'perPage',
            'page',
            'sort',
            'number',
            'name',
            'receiver',
            'debtor',
            'paymentMethod',
            'branchName',
            'marketingName',
            'driverPickupName',
            'driverDeliveryName',
            'costAmountWithService',
            'costDiscount',
            'costAmount',
            'costService',
            'costExtraCost',
            'costMargin',
            'costMethod',
            'costStatus'
        ]);
        try {
            $result = $this->reportService->getReportService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
