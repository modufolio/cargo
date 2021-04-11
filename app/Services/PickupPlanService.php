<?php
namespace App\Services;

use App\Repositories\PickupPlanRepository;
use App\Repositories\PickupRepository;
use App\Repositories\DriverRepository;
use App\Repositories\VehicleRepository;
use App\Repositories\TrackingRepository;
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
    protected $trackingRepository;

    public function __construct(
        PickupPlanRepository $pickupPlanRepository,
        DriverRepository $driverRepository,
        VehicleRepository $vehicleRepository,
        PickupRepository $pickupRepository,
        TrackingRepository $trackingRepository
    )
    {
        $this->pickupPlanRepository = $pickupPlanRepository;
        $this->driverRepository = $driverRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->pickupRepository = $pickupRepository;
        $this->trackingRepository = $trackingRepository;
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
            'branchId' => 'bail|required',
            'userId' => 'bail|required|integer',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // CHECK EVERY DATE PICKUP PLAN
        // try {
        //     $this->pickupRepository->checkPickupRequestDate($data['pickupId']);
        // } catch (Exception $e) {
        //     Log::info($e->getMessage());
        //     throw new InvalidArgumentException($e->getMessage());
        // }

        DB::beginTransaction();

        // ASSIGN DRIVER TO CURRENT VEHICLE
        try {
            $vehicle = $this->vehicleRepository->assignDriverRepo($data['vehicleId'], $data['driverId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // UPDATE BRANCH ID PADA PICKUP
        try {
            $pickup = $this->pickupRepository->updateBranchRepo($data['pickupId'], $data['branchId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal mengupdate branch pada pickup order');
        }

        // SAVE PICKUP PLAN
        try {
            $result = $this->pickupPlanRepository->savePickupPlanRepo($data['pickupId'], $data['vehicleId'], $data['userId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan pickup plan');
        }

        // CREATE TRACKING
        foreach ($data['pickupId'] as $key => $value) {
            $tracking = [
                'pickupId' => $value,
                'docs' => 'pickup-plan',
                'status' => 'applied',
                'notes' => 'petugas pickup akan menuju lokasi penjemputan',
                'picture' => null,
            ];
            try {
                $this->trackingRepository->recordTrackingByPickupRepo($tracking);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal menyimpan data tracking');
            }
        }

        DB::commit();
        return $result;
    }

    /**
     * delete pickup order
     *
     * @param array $data
     */
    public function deletePoService($data)
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
            'pickupPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->pickupPlanRepository->deletePoRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $result;
    }

    /**
     * add pickup order
     *
     * @param array $data
     */
    public function addPoService($data)
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required|array',
            'pickupPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->pickupPlanRepository->addPoRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $result;
    }

    /**
     * delete pickup plan
     *
     * @param array $data
     */
    public function deletePickupPlanService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'pickupPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        // DELETE PICKUP PLAN
        try {
            $pickupPlan = $this->pickupPlanRepository->deletePickupPlanRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        // UNASSIGN DRIVER TO CURRENT VEHICLE
        try {
            $this->vehicleRepository->unassignDriverRepo($pickupPlan);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $pickupPlan;
    }

    /**
     * cancel pickup plan
     *
     * @param array $data
     */
    public function cancelPickupPlanService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'pickupPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        // CANCEL PICKUP PLAN
        try {
            $pickupPlan = $this->pickupPlanRepository->cancelPickupPlanRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        // UNASSIGN DRIVER TO CURRENT VEHICLE
        try {
            $this->vehicleRepository->unassignDriverRepo($pickupPlan);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $pickupPlan;
    }
}
