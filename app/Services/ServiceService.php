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
            throw new InvalidArgumentException('Gagal mendapat service data');
        }
        return $fleet;
    }
}
