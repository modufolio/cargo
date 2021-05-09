<?php
namespace App\Services;

use App\Repositories\ServiceRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class ServiceService {

    protected $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Get all Service.
     *
     * @return String
     */
    public function getAll()
    {
        try {
            $fleet = $this->serviceRepository->getAll();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal mendapat service data');
        }
        return $fleet;
    }

    /**
     * get paginate
     */
    public function getPaginate($data = [])
    {
        try {
            $result = $this->serviceRepository->getPaginateRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * create service
     */
    public function createService($data = [])
    {
        $validator = Validator::make($data, [
            'name' => 'bail|required',
            'price' => 'bail|required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->serviceRepository->createServiceRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * update service
     */
    public function updateService($data = [])
    {
        $validator = Validator::make($data, [
            'name' => 'bail|required',
            'price' => 'bail|required',
            'id' => 'bail|required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->serviceRepository->updateServiceRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * delete service
     */
    public function deleteService($data = [])
    {
        $validator = Validator::make($data, [
            'serviceId' => 'bail|required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->serviceRepository->deleteServiceRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }
}
