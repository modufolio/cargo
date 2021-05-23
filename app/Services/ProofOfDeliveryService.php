<?php
namespace App\Services;

use App\Repositories\ProofOfDeliveryRepository;
use App\Repositories\PickupRepository;
use App\Repositories\ShipmentPlanRepository;
use App\Repositories\ItemRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\BillRepository;
use App\Repositories\PromoRepository;
use App\Repositories\RouteRepository;
use App\Repositories\CostRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ProofOfDeliveryService {

    protected $podRepository;
    protected $pickupRepository;
    protected $itemRepository;
    protected $trackingRepository;
    protected $billRepository;
    protected $promoRepository;
    protected $routeRepository;
    protected $costRepository;
    protected $shipmentPlanRepository;

    public function __construct(
        ProofOfDeliveryRepository $podRepository,
        PickupRepository $pickupRepository,
        ItemRepository $itemRepository,
        TrackingRepository $trackingRepository,
        BillRepository $billRepository,
        PromoRepository $promoRepository,
        RouteRepository $routeRepository,
        CostRepository $costRepository,
        ShipmentPlanRepository $shipmentPlanRepository
    )
    {
        $this->podRepository = $podRepository;
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
        $this->trackingRepository = $trackingRepository;
        $this->billRepository = $billRepository;
        $this->promoRepository = $promoRepository;
        $this->routeRepository = $routeRepository;
        $this->costRepository = $costRepository;
        $this->shipmentPlanRepository = $shipmentPlanRepository;
    }

    /**
     * create pop service
     * DEPRECATED
     * @param array $data
     * @return String
     */
    // public function createPOPService($data)
    // {
    //     $validator = Validator::make($data, [
    //         'pickupId' => 'bail|required',
    //         'notes' => 'bail|present',
    //         'driverPick' => 'bail|boolean|required',
    //         'userId' => 'bail|required',
    //         'statusPick' => 'bail|required|string'
    //     ]);

    //     if ($validator->fails()) {
    //         throw new InvalidArgumentException($validator->errors()->first());
    //     }

    //     try {
    //         $this->pickupRepository->checkPickupHasPickupPlan($data);
    //     } catch (Exception $e) {
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException($e->getMessage());
    //     }

    //     DB::beginTransaction();
    //     // CREATE POP
    //     try {
    //         $result = $this->podRepository->createPOPRepo($data);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Gagal membuat proof of pickup');
    //     }

    //     // START CALCULATE BILL
    //     try {
    //         $route = $this->routeRepository->getRouteByPickupRepo($data);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
    //     }

    //     if ($route == null) {
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
    //     }

    //     try {
    //         $promo = $this->promoRepository->getPromoByPickup($data['pickupId']);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
    //     }

    //     try {
    //         $items = $this->itemRepository->fetchItemByPickupRepo($data);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal mendapatkan items');
    //     }

    //     $items = collect($items)->values()->all();

    //     try {
    //         $bill = $this->billRepository->calculateAndSavePrice($items, $route, $promo);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menghitung total biaya');
    //     }

    //     if ($bill->success == false) {
    //         throw new InvalidArgumentException($bill->message);
    //     }

    //     $cost = [
    //         'pickupId' => $data['pickupId'],
    //         'amount' => $bill->total_price
    //     ];
    //     try {
    //         $this->costRepository->saveCostRepo($cost);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menyimpan total biaya');
    //     }
    //     // END CALCULATE BILL

    //     // CREATE TRACKING
    //     if ($data['driverPick']) {
    //         $status = 'draft';
    //         $picture = $data['picture'];
    //         if ($data['statusPick'] == 'success') {
    //             $notes = 'barang berhasil dipickup';
    //         }
    //     } else {
    //         $status = $data['popStatus'];
    //         $picture = null;
    //         if ($data['statusPick'] == 'success') {
    //             $notes = 'barang diterima digudang';
    //         }
    //     }
    //     if ($data['statusPick'] == 'failed') {
    //         $notes = 'barang gagal di pickup';
    //     }
    //     if ($data['statusPick'] == 'updated') {
    //         $notes = 'barang di pickup dengan perubahan data';
    //     }
    //     $tracking = [
    //         'pickupId' => $data['pickupId'],
    //         'docs' => 'proof-of-pickup',
    //         'status' => $status,
    //         'notes' => $notes,
    //         'picture' => $picture,
    //     ];
    //     try {
    //         $this->trackingRepository->recordTrackingByPickupRepo($tracking);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Gagal menyimpan data tracking');
    //     }

    //     DB::commit();
    //     return $result;
    // }

    /**
     * get outstanding proof of delivery
     * @param array $data
     */
    public function getOutstandingService($data = [])
    {
        $validator = Validator::make($data, [
            'perPage' => 'bail|present',
            'sort' => 'bail|present',
            'page' => 'bail|present',
            'customer' => 'bail|present',
            'pickupOrderNo' => 'bail|present',
            'shipmentPlanNumber' => 'bail|present',
            'branchId' => 'required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->podRepository->getOutstandingPickupRepo($data);
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
            'customer' => 'bail|present',
            'pickupOrderNo' => 'bail|present',
            'shipmentPlanNumber' => 'bail|present',
            'branchId' => 'required',
            'podNumber' => 'bail|present',
            'statusDelivery' => 'bail|present',
            'podStatus' => 'bail|present'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->podRepository->getSubmittedPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get pending and draft POD
     */
    public function getPendingAndDraftService($request)
    {
        try {
            $result = $this->podRepository->getPendingAndDraftRepo($request);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * update pop
     * DEPRECATED
     * @param array $data
     */
    // public function updatePOPService($data = [])
    // {
    //     $validator = Validator::make($data, [
    //         'pickup' => 'bail|required',
    //     ]);

    //     if ($validator->fails()) {
    //         throw new InvalidArgumentException($validator->errors()->first());
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $items = $this->itemRepository->updatePickupItemsRepo($data['pickup']);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException($e->getMessage());
    //     }

    //     // START CALCULATE BILL
    //     $route = ['pickupId' => $data['pickup']['id']];
    //     try {
    //         $route = $this->routeRepository->getRouteByPickupRepo($route);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
    //     }

    //     if ($route == null) {
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
    //     }

    //     try {
    //         $promo = $this->promoRepository->getPromoByPickup($data['pickup']['id']);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
    //     }

    //     // try {
    //     //     $items = $this->itemRepository->fetchItemByPickupRepo($data);
    //     // } catch (Exception $e) {
    //     //     DB::rollback();
    //     //     Log::info($e->getMessage());
    //     //     Log::error($e);
    //     //     throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal mendapatkan items');
    //     // }

    //     $items = collect($items)->values()->all();

    //     try {
    //         $bill = $this->billRepository->calculateAndSavePrice($items, $route, $promo);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menghitung total biaya');
    //     }

    //     if ($bill->success == false) {
    //         throw new InvalidArgumentException($bill->message);
    //     }

    //     $cost = [
    //         'pickupId' => $data['pickup']['id'],
    //         'amount' => $bill->total_price
    //     ];
    //     try {
    //         $this->costRepository->updateOrCreateCostByPickupIdRepo($cost);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menyimpan total biaya');
    //     }
    //     // END CALCULATE BILL

    //     try {
    //         $pickup = $this->podRepository->updatePopRepo($data['pickup']);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         Log::info($e->getMessage());
    //         Log::error($e);
    //         throw new InvalidArgumentException($e->getMessage());
    //     }
    //     DB::commit();
    //     return ['pickup' => $pickup, 'items' => $items];
    // }

    /**
     * get detail pickup for admin
     * @param array $data
     */
    public function getDetailPickupAdmin($data = [])
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->podRepository->getDetailPickupAdminRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * update status delivery pod
     */
    public function updateStatusDeliveryPODService($data = [])
    {
        $validator = Validator::make($data, [
            'statusDelivery' => 'required',
            'userId' => 'required',
            'pickupId' => 'required',
            'notes' => 'bail'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        if ($data['statusDelivery'] == 're-delivery') {
            $status = 'submitted';
            $trackingNotes = 'Pengiriman ulang ('.$data['notes'].')';
            try {
                $totalRedelivery = $this->podRepository->getTotalRedelivery($data);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException($e->getMessage());
            }
            if ($totalRedelivery >= 3) {
                DB::rollback();
                throw new InvalidArgumentException('Order ini tidak dapat dilakukan pengiriman ulang');
            } else {
                $totalRedelivery += 1;
            }
        }
        if ($data['statusDelivery'] == 'failed') {
            $status = 'applied';
            $trackingNotes = 'Pengiriman gagal';
            $totalRedelivery = 0;
        }
        if ($data['statusDelivery'] == 'success') {
            $status = 'applied';
            $trackingNotes = 'Pengiriman berhasil';
            $totalRedelivery = 0;
        }

        // SAVE STATUS DELIVERY POD
        $payload = [
            'statusDelivery' => $data['statusDelivery'],
            'status' => $status,
            'pickupId' => $data['pickupId'],
            'notes' => $data['notes'],
            'userId' => $data['userId'],
            'totalRedelivery' => $totalRedelivery
        ];
        try {
            $result = $this->podRepository->submitPODRepo($payload);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // RECORD TRACKING
        $tracking = [
            'pickupId' => $data['pickupId'],
            'docs' => 'proof-of-delivery',
            'status' => $status,
            'statusDelivery' => $data['statusDelivery'],
            'notes' => $trackingNotes
        ];

        try {
            $this->trackingRepository->recordTrackingPOD($tracking);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data tracking');
        }
        // END OF RECORD TRACKING
        DB::commit();
        return $result;
    }

    /**
     * redelivery POD
     */
    public function redeliveryPODService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'pickupId' => 'bail|required',
            'notes' => 'bail|present'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        // CHECK REDELIVERY COUNT
        try {
            $totalRedelivery = $this->podRepository->getTotalRedelivery($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            DB::rollback();
            throw new InvalidArgumentException($e->getMessage());
        }
        // CHECK REDELIVERY COUNT

        // VALIDATE REDELIVERY COUNT
        if ($totalRedelivery >= 3) {
            DB::rollback();
            throw new InvalidArgumentException('Order ini tidak dapat dilakukan pengiriman ulang');
        } else {
            $totalRedelivery += 1;
        }
        // VALIDATE REDELIVERY COUNT

        // UPDATE REDELIVERY COUNT
        try {
            $result = $this->podRepository->updateRedeliveryCount($data['pickupId'], $totalRedelivery);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        // UPDATE REDELIVERY COUNT

        // RECORD TRACKING
        $tracking = [
            'pickupId' => $data['pickupId'],
            'docs' => 'proof-of-delivery',
            'status' => 'submitted',
            'statusDelivery' => 're-delivery',
            'notes' => $data['notes'] ?? 'Order akan dikirim ulang'
        ];

        try {
            $this->trackingRepository->recordTrackingPOD($tracking);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data tracking');
        }
        // END OF RECORD TRACKING
        DB::commit();
        return $result;
    }

    /**
     * submit POD by driver
     */
    public function submitPODDriver($data = [])
    {
        $validator = Validator::make($data, [
            'statusDelivery' => 'bail|required',
            'userId' => 'bail|required',
            'pickupId' => 'bail|required',
            'notes' => 'bail',
            'picture' => 'bail|present'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        if ($data['statusDelivery'] == 're-delivery') {
            $trackingNotes = 'Pengiriman ulang ('.$data['notes'].')';
            try {
                $totalRedelivery = $this->podRepository->getTotalRedelivery($data);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException($e->getMessage());
            }
            if ($totalRedelivery >= 3) {
                throw new InvalidArgumentException('Order ini tidak dapat dilakukan pengiriman ulang');
            } else {
                $totalRedelivery += 1;
            }
        }
        if ($data['statusDelivery'] == 'failed') {
            $trackingNotes = 'Pengiriman gagal';
            $totalRedelivery = 0;
        }
        if ($data['statusDelivery'] == 'success') {
            $trackingNotes = 'Pengiriman berhasil';
            $totalRedelivery = 0;
        }

        // SAVE STATUS DELIVERY POD
        $payload = [
            'statusDelivery' => $data['statusDelivery'],
            'status' => 'draft',
            'pickupId' => $data['pickupId'],
            'notes' => $data['notes'],
            'userId' => $data['userId'],
            'totalRedelivery' => $totalRedelivery
        ];
        try {
            $result = $this->podRepository->submitPODRepo($payload);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // RECORD TRACKING
        $tracking = [
            'pickupId' => $data['pickupId'],
            'docs' => 'proof-of-delivery',
            'status' => 'draft',
            'statusDelivery' => $data['statusDelivery'],
            'notes' => $trackingNotes,
            'picture' => $data['picture'] ?? null
        ];

        try {
            $this->trackingRepository->recordTrackingPODDriver($tracking);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data tracking');
        }
        // END OF RECORD TRACKING
        DB::commit();
        return $result;
    }

    /**
     * get pickup order in POD by driver
     */
    public function getPickupList($data = [])
    {
        try {
            $result = $this->podRepository->getPickupListRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get dashboard POD for driver service
     */
    public function getDashboardDriverService($data = [])
    {
        $validator = Validator::make($data, [
            'userId' => 'bail|required',
            'startDate' => 'bail|present',
            'endDate' => 'bail|present'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->shipmentPlanRepository->getDashboardDriverPODRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        return $result;
    }
}
