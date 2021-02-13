<?php
namespace App\Services;

use App\Repositories\PickupPlanRepository;
use App\Repositories\PickupRepository;
use App\Repositories\DriverRepository;
use App\Repositories\VehicleRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class PickupPlanService {

    protected $pickupPlanRepository;
    protected $pickupRepository;
    protected $driverRepository;
    protected $vehicleRepository;

    public function __construct(
        PickupPlanRepository $pickupPlanRepository,
        DriverRepository $driverRepository,
        VehicleRepository $vehicleRepository,
        PickupRepository $pickupRepository
    )
    {
        $this->pickupPlanRepository = $pickupPlanRepository;
        $this->driverRepository = $driverRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->pickupRepository = $pickupRepository;
    }

    /**
     * save pickup plan
     *
     * @param array $data
     * @return String
     */
    public function savePickupPlanService($data)
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required|array',
            'vehicleId' => 'bail|required|integer',
            'driverId' => 'bail|required|integer',
            'userId' => 'bail|required|integer',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // CHECK EVERY DATE PICKUP PLAN
        try {
            $this->pickupRepository->checkPickupRequestDate($data['pickupId']);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::beginTransaction();

        // ASSIGN DRIVER TO CURRENT VEHICLE
        try {
            $vehicle = $this->vehicleRepository->assignDriverRepo($data['vehicleId'], $data['driverId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        try {
            $result = $this->pickupPlanRepository->savePickupPlanRepo($data['pickupId'], $data['vehicleId'], $data['userId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan pickup plan');
        }
        DB::commit();
        return $result;
    }
}
