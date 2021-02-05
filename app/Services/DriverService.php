<?php
namespace App\Services;

// REPOSITORY
use App\Repositories\DriverRepository;
use App\Repositories\UserRepository;
use App\Repositories\AddressRepository;

// GENERAL
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class DriverService {

    protected $driverRepository;
    protected $userRepository;
    protected $addressRepository;

    public function __construct(AddressRepository $addressRepository, DriverRepository $driverRepository, UserRepository $userRepository)
    {
        $this->driverRepository = $driverRepository;
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
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

    /**
     * Get all driver pagination
     */
    public function getAllPaginateService($data)
    {
        try {
            $result = $this->driverRepository->getAllPaginateRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * edit driver
     */
    public function editDriverService($data = [])
    {
        $validator = Validator::make($data, [
            'id' => 'bail|required|max:50',
            'name' => 'bail|required|max:255',
            'phone' => 'bail|required|max:255',
            'email' => 'bail|required|max:255',
            'active' => 'bail|required|boolean',
            'branchId' => 'bail|required|max:50',
            'type' => 'bail|required|max:50',
            'province' => 'bail|required|max:255',
            'city' => 'bail|required|max:255',
            'district' => 'bail|required|max:255',
            'village' => 'bail|required|max:255',
            'street' => 'bail|required|max:255',
            'postalCode' => 'bail|required|max:255',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        // UPDATE USER
        try {
            $user = $this->userRepository->updateUserOfDriver($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        // UPDATE ADDRESS
        $address = [
            'postal_code' => $data['postalCode']
        ];
        $data = array_merge($address, $data);
        try {
            $this->addressRepository->update($data, $user->id);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        try {
            $result = $this->driverRepository->editDriverRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $result;
    }

    public function createDriverService($data, $userId)
    {
        $validator = Validator::make($data, [
            'type' => 'bail|required|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->driverRepository->createDriverRepo($data, $userId);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan data driver');
        }
        return $result;
    }

    public function disableDriverService($data)
    {
        $validator = Validator::make($data, [
            'driverId' => 'bail|required|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $result = $this->driverRepository->disableDriverRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::commit();
        return $result;
    }
}
