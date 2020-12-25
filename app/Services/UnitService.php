<?php
namespace App\Services;

use App\Repositories\UnitRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class UnitService {

    protected $unitRepository;

    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    /**
     * Get all unit.
     *
     * @return String
     */
    public function getAll()
    {
        try {
            $fleet = $this->unitRepository->getAll();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapat data unit');
        }
        return $fleet;
    }
}
