<?php
namespace App\Services;

use App\Repositories\VehicleRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class VehicleService {

    protected $vehicleRepository;

    public function __construct(VehicleRepository $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    /**
     * Get vehicle
     *
     * @param array $data
     * @return String
     */
    public function getVehicleService($data)
    {
        $validator = Validator::make($data, [
            'value' => 'bail|required|max:50',
            'type' => 'bail|required|max:50'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        if ($data['type'] == 'number') {
            try {
                $result = $this->vehicleRepository->getAllVehicleByNumberRepo($data);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException('Gagal mendapat data kendaraan');
            }
        } else {
            try {
                $result = $this->vehicleRepository->getAvailableVehicleByNameRepo($data);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException('Gagal mendapat data kendaraan');
            }
        }

        return $result;
    }

    /**
     * Pagination vehicle
     */
    public function paginateVehicleService($data = [])
    {
        try {
            $result = $this->vehicleRepository->vehiclePaginationRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data kendaraan');
        }
        return $result;
    }

    /**
     * edit vehicle
     */
    public function editVehicleService($data = [])
    {
        $validator = Validator::make($data, [
            'id' => 'bail|required|max:50',
            'licensePlate' => 'bail|required|max:50',
            'name' => 'bail|required|max:50',
            'type' => 'bail|required|max:50',
            'maxVolume' => 'bail|required|max:99999',
            'maxWeight' => 'bail|required|max:99999',
            'status' => 'bail|required|max:50',
            'active' => 'bail|required|boolean',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->vehicleRepository->editVehicleRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mengubah data kendaraan');
        }
        DB::commit();
        return $result;
    }

    /**
     * edit vehicle
     */
    public function createVehicleService($data = [])
    {
        $validator = Validator::make($data, [
            'licensePlate' => 'bail|required|max:50',
            'name' => 'bail|required|max:50',
            'type' => 'bail|required|max:50',
            'maxVolume' => 'bail|required|max:99999|numeric',
            'maxWeight' => 'bail|required|max:99999|numeric',
            'status' => 'bail|required|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->vehicleRepository->createVehicleRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menambahkan data kendaraan');
        }
        DB::commit();
        return $result;
    }

    /**
     * delete vehicle service
     *
     * @param array $data
     */
    public function deleteVehicleService($data = [])
    {
        $validator = Validator::make($data, [
            'vehicleId' => 'bail|required|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->vehicleRepository->deleteVehicleRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     * Get ten vehicle
     *
     * @return String
     */
    public function getTenVehicleService()
    {
        try {
            $result = $this->vehicleRepository->getTenVehicleRepo();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data kendaraan');
        }
        return $result;
    }
}
