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
                $result = $this->vehicleRepository->getVehicleByNumberRepo($data);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException('Gagal mendapat data kendaraan');
            }
        } else {
            try {
                $result = $this->vehicleRepository->getVehicleByNameRepo($data);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException('Gagal mendapat data kendaraan');
            }
        }

        return $result;
    }
}
