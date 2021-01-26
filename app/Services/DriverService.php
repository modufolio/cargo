<?php
namespace App\Services;

use App\Repositories\DriverRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class DriverService {

    protected $driverRepository;

    public function __construct(DriverRepository $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }

    /**
     * Get driver by vehicle
     *
     * @param array $data
     * @return String
     */
    public function getDriverService($data)
    {
        $validator = Validator::make($data, [
            'value' => 'bail|required|max:50',
            'type' => 'bail|required|max:50'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        if ($data['type'] == 'id') {
            try {
                $result = $this->driverRepository->getAvailableDriverByVehicleRepo($data['value']);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException('Gagal mendapat data driver');
            }
        } else {
            try {
                $result = $this->driverRepository->getAvailableDriverByNameRepo($data['value']);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                throw new InvalidArgumentException('Gagal mendapat data driver');
            }
        }

        return $result;
    }
}
