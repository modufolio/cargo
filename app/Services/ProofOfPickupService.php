<?php
namespace App\Services;

use App\Repositories\ProofOfPickupRepository;
use App\Repositories\PickupRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ProofOfPickupService {

    protected $proofOfPickupRepository;
    protected $pickupRepository;

    public function __construct(ProofOfPickupRepository $proofOfPickupRepository, PickupRepository $pickupRepository)
    {
        $this->popRepository = $proofOfPickupRepository;
        $this->pickupRepository = $pickupRepository;
    }

    /**
     * create pop service
     *
     * @param array $data
     * @return String
     */
    public function createPOPService($data)
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
            'notes' => 'bail|present',
            'driverPick' => 'bail|boolean|required',
            'userId' => 'bail|required',
            'status' => 'bail|required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $this->pickupRepository->checkPickupHasPickupPlan($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::beginTransaction();
        try {
            $result = $this->popRepository->createPOPRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat proof of pickup');
        }
        DB::commit();
        return $result;
    }

    /**
     * get outstanding proof of pickup
     * @param array $data
     */
    public function getOutstandingService($data = [])
    {
        $validator = Validator::make($data, [
            'perPage' => 'bail|present',
            'sort' => 'bail|present',
            'page' => 'bail|present',
            'general' => 'bail|present',
            'customer' => 'bail|present',
            'pickupOrderNo' => 'bail|present',
            'requestPickupDate' => 'bail|present',
            'pickupPlanNo' => 'bail|present',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->popRepository->getOutstandingPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }
}
