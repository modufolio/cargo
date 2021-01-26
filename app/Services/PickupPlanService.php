<?php
namespace App\Services;

use App\Repositories\PickupPlanRepository;
use App\Repositories\DriverRepository;
use App\Repositories\VehicleRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class PickupPlanService {

    protected $pickupPlanRepository;
    protected $driverRepository;
    protected $vehicleRepository;

    public function __construct(
        PickupPlanRepository $pickupPlanRepository,
        DriverRepository $driverRepository,
        VehicleRepository $vehicleRepository
    )
    {
        $this->pickupPlanRepository = $pickupPlanRepository;
        $this->driverRepository = $driverRepository;
        $this->vehicleRepository = $vehicleRepository;
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
