<?php
namespace App\Services;

use App\Repositories\ShipmentPlanRepository;
use App\Repositories\PickupRepository;
use App\Repositories\DriverRepository;
use App\Repositories\VehicleRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\BranchRepository;
use App\Repositories\TransitRepository;
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
    protected $trackingRepository;
    protected $branchRepository;
    protected $transitRepository;

    public function __construct(
        ShipmentPlanRepository $shipmentPlanRepository,
        DriverRepository $driverRepository,
        VehicleRepository $vehicleRepository,
        PickupRepository $pickupRepository,
        TrackingRepository $trackingRepository,
        BranchRepository $branchRepository,
        TransitRepository $transitRepository
    )
    {
        $this->shipmentPlanRepository = $shipmentPlanRepository;
        $this->driverRepository = $driverRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->pickupRepository = $pickupRepository;
        $this->trackingRepository = $trackingRepository;
        $this->branchRepository = $branchRepository;
        $this->transitRepository = $transitRepository;
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
            'fleet' => 'bail|present',
            'withFleet' => 'bail|present'
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

        // CHECK TRANSIT
        if ($data['isTransit']) {

            $validator = Validator::make($data, [
                'transitBranch' => 'bail|required',
            ]);

            if ($validator->fails()) {
                DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }

            $notes = 'paket ditransit ke cabang: '.$data['transitBranch']['name'];
            $docs = 'transit';
            $status = 'pending';
            // UPDATE PICKUP BRANCH
            try {
                $branchFrom = $this->pickupRepository->updateBranchRepo($data['pickupId'], $data['transitBranch']['id']);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal mengupdate data cabang pada pickup order');
            }
            // UPDATE IS TRANSIT BRANCH
            try {
                $this->pickupRepository->updateIsTransitBranchRepo($data['pickupId'], true);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal mengupdate data cabang pada pickup order');
            }

            // TRANSIT HISTORY
            foreach ($data['pickupId'] as $key => $value) {
                // $branchFrom = $this->branchRepository->checkBranchByPickupRepo($value);
                $transitData = [
                    'pickupId' => $value,
                    'status' => 'pending',
                    'received' => false,
                    'notes' => $notes,
                    'userId' => $data['userId']
                ];
                try {
                    $this->transitRepository->saveTransitRepo($transitData);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException('Gagal menyimpan transit data');
                }
            }
        } else {
            $notes = 'paket dikirim ke alamat tujuan';
            $docs = 'shipment-plan';
            $status = 'applied';
            // SAVE SHIPMENT PLAN
            try {
                $shipmentPlan = $this->shipmentPlanRepository->saveShipmentPlanRepo($data['pickupId'], $data['vehicleId'], $data['userId']);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal menyimpan shipment plan');
            }

            // UPDATE IS TRANSIT BRANCH
            try {
                $this->pickupRepository->updateIsTransitBranchRepo($data['pickupId'], false);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal mengupdate data cabang pada pickup order');
            }

            // GET PICKUP BRANCH
            try {
                $branchFrom = $this->pickupRepository->getPickupBranchRepo($data['pickupId']);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal mengupdate data cabang pada pickup order');
            }
        }

        // CREATE TRACKING
        foreach ($data['pickupId'] as $key => $value) {
            $tracking = [
                'pickupId' => $value,
                'docs' => $docs,
                'status' => $status,
                'notes' => $notes,
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

            $branch = collect($branchFrom)->firstWhere('id', $value);
            $driverLog = [
                'pickupId' => $value,
                'driverId' => $data['driverId'],
                'branchFrom' => $branch['branch_id'],
                'branchTo' => $data['isTransit'] ? $data['transitBranch']['id'] : null,
            ];
            try {
                $this->trackingRepository->recordPickupDriverLog($driverLog);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException('Gagal menyimpan data tracking driver');
            }

            if ($data['withFleet']) {
                $fleetName = $data['fleet']['name'];
                $fleetDepartureDate = $data['fleet']['departureDate'];
                $tracking = [
                    'pickupId' => $value,
                    'docs' => $docs,
                    'status' => $status,
                    'notes' => "Paket dikirim dengan armada ($fleetName) dan akan berangkat pada ($fleetDepartureDate)",
                    'picture' => null,
                ];
                try {
                    $this->trackingRepository->recordTrackingByPickupRepo($tracking);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException('Gagal menyimpan data tracking armada');
                }
            }
        }

        if ($data['withFleet']) {
            foreach ($data['pickupId'] as $key => $value) {
                $fleetData = [
                    'pickupId' => $value,
                    'fleetName' => $data['fleet']['name'],
                    'fleetDeparture' => $data['fleet']['departureDate'],
                ];
                try {
                    $this->pickupRepository->updateFleetDataPickupRepo($fleetData);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException('Gagal menyimpan data armada pada pickup');
                }
            }
        }

        DB::commit();
        return (object)['pickupId' => $data['pickupId']];
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
            'shipmentPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanRepository->deletePoRepo($data);
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
            'shipmentPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanRepository->addPoRepo($data);
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

    /**
     * cancel shipment plan
     *
     * @param array $data
     */
    public function cancelShipmentPlanService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'shipmentPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        // CANCEL SHIPMENT PLAN
        try {
            $shipmentPlan = $this->shipmentPlanRepository->cancelShipmentPlanRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // UNASSIGN PICKUP WITH CURRENT SHIPMENT PLAN
        try {
            $this->pickupRepository->cancelShipmentPlanRepo($data['shipmentPlanId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // UNASSIGN DRIVER TO CURRENT VEHICLE
        try {
            $this->vehicleRepository->unassignDriverRepo($shipmentPlan);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $shipmentPlan;
    }

    /**
     * get shipment plan driver
     */
    public function getDriverShipmentPlanListService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'required',
            'startDate' => 'bail|present',
            'endDate' => 'bail|present'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->shipmentPlanRepository->getDriverShipmentPlanListRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get list po in shipment plan by driver
     */
    public function getPickupOrderDriverShipmentPlanListService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'required',
            'shipmentPlanId' => 'required',
            'filter' => 'bail'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        // SHIPMENT PLAN
        try {
            $result = $this->shipmentPlanRepository->getPickupOrderDriverShipmentPlanListRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get dashboard shipment plan driver
     */
    public function getDashboardDriverService($data = [])
    {
        try {
            $result = $this->shipmentPlanRepository->getDashboardDriverRepo($data['shipmentPlanId']);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }
}
