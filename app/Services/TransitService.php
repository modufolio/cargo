<?php
namespace App\Services;

use App\Repositories\TransitRepository;
use App\Repositories\PickupRepository;
use App\Repositories\ItemRepository;
use App\Repositories\TrackingRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class TransitService {

    protected $transitRepository;
    protected $pickupRepository;
    protected $itemRepository;
    protected $trackingRepository;

    public function __construct(
        TransitRepository $transitRepository,
        PickupRepository $pickupRepository,
        ItemRepository $itemRepository,
        TrackingRepository $trackingRepository
    )
    {
        $this->transitRepository = $transitRepository;
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
        $this->trackingRepository = $trackingRepository;
    }

    /**
     * draft transit service
     *
     * @param array $data
     * @return String
     */
    public function draftTransitService($data)
    {
        $validator = Validator::make($data, [
            'received' => 'bail|required|boolean',
            'notes' => 'bail|required',
            'status' => 'bail|required',
            'userId' => 'bail|required',
            'transitId' => 'bail|required',
            'pickupId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->transitRepository->draftTransitRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal membuat draft transit pickup');
        }

        // CREATE TRACKING
        $tracking = [
            'pickupId' => $data['pickupId'],
            'docs' => 'transit',
            'status' => $data['status'],
            'notes' => $data['notes'],
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
            'transitNumber' => 'bail|present',
            'pickupOrderNo' => 'bail|present',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->transitRepository->getOutstandingPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get submitted proof of pickup
     * @param array $data
     */
    public function getSubmittedService($data = [])
    {
        $validator = Validator::make($data, [
            'perPage' => 'bail|present',
            'sort' => 'bail|present',
            'page' => 'bail|present',
            'general' => 'bail|present',
            'customer' => 'bail|present',
            'transitNumber' => 'bail|present',
            'pickupOrderNo' => 'bail|present',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->transitRepository->getSubmittedPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get pending and draft transit pickup
     */
    public function getPendingAndDraftService()
    {
        try {
            $result = $this->transitRepository->getPendingAndDraftRepo();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * update pop
     * @param array $data
     */
    public function updatePOPService($data = [])
    {
        $validator = Validator::make($data, [
            'pickup' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $items = $this->itemRepository->updatePickupItemsRepo($data['pickup']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }

        try {
            $pickup = $this->popRepository->updatePopRepo($data['pickup']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return ['pickup' => $pickup, 'items' => $items];
    }
}
