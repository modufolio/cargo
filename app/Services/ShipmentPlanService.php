<?php
namespace App\Services;

use App\Repositories\ShipmentPlanRepository;
use App\Repositories\PickupRepository;
use App\Repositories\DriverRepository;
use App\Repositories\VehicleRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class ShipmentPlanService {

    protected $shipmentPlanRepository;
    protected $pickupRepository;
    protected $driverRepository;
    protected $vehicleRepository;

    public function __construct(
        ShipmentPlanRepository $shipmentPlanRepository,
        DriverRepository $driverRepository,
        VehicleRepository $vehicleRepository,
        PickupRepository $pickupRepository
    )
    {
        $this->shipmentPlanRepository = $shipmentPlanRepository;
        $this->driverRepository = $driverRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->pickupRepository = $pickupRepository;
    }

    /**
     * save shipment plan
     *
     * @param array $data
     * @return String
     */
    public function saveShipmentPlanService($data)
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

        // CHECK EVERY DATE SHIPMENT PLAN
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

        // SAVE SHIPMENT PLAN
        try {
            $result = $this->shipmentPlanRepository->saveShipmentPlanRepo($data['pickupId'], $data['vehicleId'], $data['userId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan shipment plan');
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
