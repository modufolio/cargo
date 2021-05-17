<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\ReportRepository;

use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ReportService {

    protected $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * get reporting pickup
     */
    public function getReportPickupWithRangeService($data)
    {
        $validator = Validator::make($data, [
			'startDate' => 'bail|present',
			'endDate' => 'bail|present'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

        try {
            $pickup = $this->reportRepository->getReportPickupWithRangeRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * get reporting of success order
     */
    public function getReportSuccessPickupWithRangeService($data)
    {
        $validator = Validator::make($data, [
			'startDate' => 'bail|present',
			'endDate' => 'bail|present'
		]);

		if ($validator->fails()) {
			throw new InvalidArgumentException($validator->errors()->first());
		}

        try {
            $pickup = $this->reportRepository->getReportSuccessOrderRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }
}
