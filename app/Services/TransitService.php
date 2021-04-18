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
            'statusPick' => 'bail|required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $this->pickupRepository->checkPickupHasPickupPlan($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        DB::beginTransaction();
        try {
            $result = $this->popRepository->createPOPRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal membuat proof of pickup');
        }

        // CREATE TRACKING
        if ($data['driverPick']) {
            $status = 'draft';
            $picture = $data['picture'];
            if ($data['statusPick'] == 'success') {
                $notes = 'barang berhasil dipickup';
            }
        } else {
            $status = $data['popStatus'];
            $picture = null;
            if ($data['statusPick'] == 'success') {
                $notes = 'barang diterima digudang';
            }
        }
        if ($data['statusPick'] == 'failed') {
            $notes = 'barang gagal di pickup';
        }
        if ($data['statusPick'] == 'updated') {
            $notes = 'barang di pickup dengan perubahan data';
        }
        $tracking = [
            'pickupId' => $data['pickupId'],
            'docs' => 'proof-of-pickup',
            'status' => $status,
            'notes' => $notes,
            'picture' => $picture,
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
            'popNumber' => 'bail|present',
            'popDate' => 'bail|present',
            'poNumber' => 'bail|present',
            'popStatus' => 'bail|present',
            'poStatus' => 'bail|present',
            'poCreatedDate' => 'bail|present',
            'poPickupDate' => 'bail|present',
            'pickupPlanNumber' => 'bail|present',
            'driverPick' => 'bail|present',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->popRepository->getSubmittedPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
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
