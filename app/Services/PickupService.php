<?php
namespace App\Services;

// use App\Models\Pickup;
use App\Repositories\PickupRepository;
use App\Repositories\ItemRepository;
use App\Repositories\BillRepository;
use App\Repositories\PromoRepository;
use App\Repositories\RouteRepository;
use App\Repositories\AddressRepository;
use App\Repositories\ProofOfPickupRepository;
use App\Repositories\DebtorRepository;
use App\Repositories\ReceiverRepository;
use App\Repositories\SenderRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\UserRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class PickupService {

    protected $pickupRepository;
    protected $itemRepository;
    protected $billRepository;
    protected $promoRepository;
    protected $routeRepository;
    protected $addressRepository;
    protected $senderRepository;
    protected $receiverRepository;
    protected $debtorRepository;
    protected $proofOfPickupRepository;
    protected $trackingRepository;
    protected $userRepository;

    public function __construct(
        PickupRepository $pickupRepository,
        ItemRepository $itemRepository,
        BillRepository $billRepository,
        PromoRepository $promoRepository,
        RouteRepository $routeRepository,
        AddressRepository $addressRepository,
        ProofOfPickupRepository $proofOfPickupRepository,
        SenderRepository $senderRepository,
        ReceiverRepository $receiverRepository,
        DebtorRepository $debtorRepository,
        TrackingRepository $trackingRepository,
        UserRepository $userRepository
    )
    {
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
        $this->billRepository = $billRepository;
        $this->promoRepository = $promoRepository;
        $this->routeRepository = $routeRepository;
        $this->addressRepository = $addressRepository;
        $this->popRepository = $proofOfPickupRepository;
        $this->senderRepository = $senderRepository;
        $this->receiverRepository = $receiverRepository;
        $this->debtorRepository = $debtorRepository;
        $this->trackingRepository = $trackingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Validate pickup data.
     * Store to DB if there are no errors.
     * @param array $data
     */
    public function createPickupService($data)
    {
        $validator = Validator::make($data, [
            'fleetId'               => 'bail|required|max:19',
            'userId'                => 'bail|required|max:19',
            'promoId'               => 'bail|nullable|max:19',
            'name'                  => 'bail|required|max:255',
            'phone'                 => 'bail|required|max:14',
            'senderId'              => 'bail|required|max:19',
            'receiverId'            => 'bail|required|max:19',
            'debtorId'              => 'bail|required|max:19',
            'notes'                 => 'bail|required|max:500',
            'picktime'              => 'bail|date',
            'origin'                => 'bail|required|max:50',
            'destination_city'      => 'bail|required|max:50',
            'destination_district'  => 'bail|required|max:50',
            'items'                 => 'bail|required|array'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        // VALIDATE SENDER, RECEIVER, AND DEBTOR
        try {
            $address = $this->addressRepository->validateAddress($data, $data['userId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // PROMO
        if ($data['promoId'] !== null) {
            if ($data['promoId'] !== '') {
                try {
                    $promo = $this->promoRepository->getById($data['promoId']);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException($e->getMessage());
                }

                if ($promo !== false) {
                    if ($promo['user_id'] !== $data['userId']) {
                        DB::rollback();
                        throw new InvalidArgumentException('Promo tidak dapat digunakan');
                    }
                }
            }
        }
        if ($data['promoId'] == null || $data['promoId'] == '') {
            $promo = null;
        }
        // END PROMO

        // GET ROUTE
        try {
            $route = $this->routeRepository->getRouteRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
        }

        if (!$route) {
            DB::rollback();
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }
        // END GET ROUTE

        // SAVE PICKUP
        $data['senderId']   = $address['sender']['id'];
        $data['debtorId']   = $address['debtor']['id'];
        $data['receiverId'] = $address['receiver']['id'];
        try {
            $pickup = $this->pickupRepository->createPickupRepo($data, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data pickup');
        }
        // END SAVE PICKUP

        // SAVE ITEM
        try {
            $items = $this->itemRepository->save($pickup, $data['items']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan item / barang');
        }
        // END SAVE ITEM

        // CALCULATE PRICE
        try {
            $price = $this->billRepository->calculatePrice($items, $route, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
        }

        if (!$price->success) {
            DB::rollback();
            throw new InvalidArgumentException($price->message);
        }
        // END CALCULATE PRICE

        // CREATE TRACKING
        $tracking = [
            'pickupId' => $pickup['id'],
            'docs' => 'pickup',
            'status' => 'request',
            'notes' => 'pengajuan pickup order telah diterima',
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
        // END CREATE TRACKING

        DB::commit();
        $result = (object)[
            'items' => $items,
            'route' => $route,
            'pickup' => $pickup,
            'promo' => $promo,
            'price' => $price
        ];
        return $result;
    }

    /**
     * Get all pickup paginate
     * @param array $data
     */
    public function getAllPaginate($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getAllPickupPaginate($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan data pickup');
        }
        return $pickup;
    }

    /**
     * Get all pickup paginate
     * @param array $data
     */
    public function getReadyToPickupService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getReadyToPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan data pickup');
        }
        return $pickup;
    }

    /**
     * get list pickup plan paginate
     * @param array $data
     */
    public function getListPickupPlanService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getListPickupPlanRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * get list pickup inside pickup plan
     * @param array $data
     */
    public function getPickupByPickupPlanService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getPickupByPickupPlanRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * Get pickup paginate by customer id
     * @param array $data
     */
    public function getPickupPaginateByUserId($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getPickupByCustomerRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan data pickup');
        }
        return $pickup;
    }

    /**
     * get pickup plan current driver
     * @param array $data
     */
    public function getDriverPickupPlanListService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getListPickupPlanDriverRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * Get all pickup inside pickup plan paginate
     * @param array $data
     */
    public function getReadyToPickupDriverService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getReadyToPickupDriverRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan data pickup');
        }
        return $pickup;
    }

    /**
     * get list pickup inside pickup plan
     * driver only
     * @param array $data
     */
    public function getPickupByPickupPlanDriverService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getPickupByPickupPlanDriverRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * get total volume and kilo in pickup of pickup plan
     * @param array $data
     */
    public function getTotalVolumeAndKiloService($data = [])
    {
        $validator = Validator::make($data, [
            'pickupPlanId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->pickupRepository->getTotalVolumeAndKiloPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * get detail pickup
     * @param array $data
     */
    public function getDetailPickup($data = [])
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->pickupRepository->getDetailPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

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
            $result = $this->pickupRepository->getDetailPickupAdminRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }

    /**
     * create pickup service by admin
     */
    public function createPickupAdminService($data = [])
    {
        $validator = Validator::make($data, [
            'items' => 'bail|required',
            'userId' => 'bail|required',
            'form' => 'bail|required',
            'customer' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        // create user
        if ($data['form']['newCustomer']) {
            $username = explode("@", $request->email, 2);
            $payload = [
                'email' => $data['customer']['email'],
                'name' => $data['customer']['name'],
                'username' => $username,
                'role_id' => 1,
                'branch_id' => $data['customer']['branchId'],
                'phone' => $data['customer']['phone'],
                'password' => 'user1234'
            ];
            try {
                $customer = $this->userRepository->save($payload);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException($e->getMessage());
            }
        } else {
            try {
                $customer = $this->userRepository->getByEmail($data['form']['email']);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                Log::error($e);
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        // customer
        $data['form']['sender']['is_primary'] = $data['form']['receiver']['is_primary'] = $data['form']['debtor']['is_primary'] = false;
        $data['form']['sender']['temporary'] = $data['form']['receiver']['temporary'] = $data['form']['debtor']['temporary'] = true;
        $data['form']['sender']['title'] = $data['form']['receiver']['title'] = $data['form']['debtor']['title'] = $data['form']['name'];
        $data['form']['sender']['userId'] = $data['form']['receiver']['userId'] = $data['form']['debtor']['userId'] = $customer['id'];

        // user web
        $data['form']['userId'] = $data['userId'];

        // save sender
        try {
            $sender = $this->senderRepository->save($data['form']['sender']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // save debtor
        try {
            $debtor = $this->debtorRepository->save($data['form']['debtor']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // save receiver
        try {
            $receiver = $this->receiverRepository->save($data['form']['receiver']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        // PROMO
        if ($data['form']['promoId'] !== null) {
            if ($data['form']['promoId'] !== '') {
                try {
                    $promo = $this->promoRepository->getById($data['form']['promoId']);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException($e->getMessage());
                }

                if ($promo !== false) {
                    if ($promo['user_id'] !== $data['userId']) {
                        DB::rollback();
                        throw new InvalidArgumentException('Promo tidak dapat digunakan');
                    }
                }
            }
        }
        if ($data['form']['promoId'] == null || $data['form']['promoId'] == '') {
            $promo = null;
        }
        // END PROMO

        // GET ROUTE
        try {
            $route = $this->routeRepository->getRouteRepo($data['form']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
        }

        if (!$route) {
            DB::rollback();
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }
        // END GET ROUTE

        // SAVE PICKUP
        $data['form']['senderId'] = $sender['id'];
        $data['form']['receiverId'] = $receiver['id'];
        $data['form']['debtorId'] = $debtor['id'];
        try {
            $pickup = $this->pickupRepository->createPickupAdminRepo($data['form'], $promo, $customer);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data pickup');
        }
        // END SAVE PICKUP

        // SAVE ITEM
        $items = $data['items'];
        foreach ($items as $key => $answer) {
            unset($items[$key]['service']);
        }
        try {
            $items = $this->itemRepository->save($pickup, $items);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan item / barang');
        }
        // END SAVE ITEM

        // CALCULATE PRICE
        try {
            $price = $this->billRepository->calculatePrice($items, $route, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
        }

        if (!$price->success) {
            DB::rollback();
            throw new InvalidArgumentException($price->message);
        }
        // END CALCULATE PRICE

        DB::commit();
        $result = (object)[
            'items' => $items,
            'route' => $route,
            'pickup' => $pickup,
            'promo' => $promo,
            'price' => $price
        ];
        return $result;
    }

    /**
     * Get all pickup paginate where ready to shipment
     * @param array $data
     */
    public function getReadyToShipmentService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getReadyToShipmentRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan data pickup');
        }
        return $pickup;
    }

    /**
     * get list shipment plan paginate
     * @param array $data
     */
    public function getListShipmentPlanService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getListShipmentPlanRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * get list pickup inside shipment plan
     * @param array $data
     */
    public function getPickupByShipmentPlanService($data = [])
    {
        try {
            $pickup = $this->pickupRepository->getPickupByShipmentPlanRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $pickup;
    }

    /**
     * create pickup service by admin
     */
    public function createDropService($data = [])
    {
        $validator = Validator::make($data, [
            'items' => 'bail|required',
            'userId' => 'bail|required',
            'form' => 'bail|required',
            'branchId' => 'required'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();

        $data['form']['sender']['is_primary'] = $data['form']['receiver']['is_primary'] = $data['form']['debtor']['is_primary'] = false;
        $data['form']['sender']['temporary'] = $data['form']['receiver']['temporary'] = $data['form']['debtor']['temporary'] = true;
        $data['form']['sender']['title'] = $data['form']['receiver']['title'] = $data['form']['debtor']['title'] = $data['form']['name'];
        $data['form']['sender']['userId'] = $data['form']['receiver']['userId'] = $data['form']['debtor']['userId'] = $data['userId'];
        $data['form']['userId'] = $data['userId'];

        // save sender
        try {
            $sender = $this->senderRepository->save($data['form']['sender']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // save debtor
        try {
            $debtor = $this->debtorRepository->save($data['form']['debtor']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }

        // save receiver
        try {
            $receiver = $this->receiverRepository->save($data['form']['receiver']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException($e->getMessage());
        }
        // PROMO
        if ($data['form']['promoId'] !== null) {
            if ($data['form']['promoId'] !== '') {
                try {
                    $promo = $this->promoRepository->getById($data['form']['promoId']);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::info($e->getMessage());
                    Log::error($e);
                    throw new InvalidArgumentException($e->getMessage());
                }

                if ($promo !== false) {
                    if ($promo['user_id'] !== $data['userId']) {
                        DB::rollback();
                        throw new InvalidArgumentException('Promo tidak dapat digunakan');
                    }
                }
            }
        }
        if ($data['form']['promoId'] == null || $data['form']['promoId'] == '') {
            $promo = null;
        }
        // END PROMO

        // GET ROUTE
        try {
            $route = $this->routeRepository->getRouteRepo($data['form']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
        }

        if (!$route) {
            DB::rollback();
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }
        // END GET ROUTE

        // SAVE DROP ORDER
        $data['form']['senderId'] = $sender['id'];
        $data['form']['receiverId'] = $receiver['id'];
        $data['form']['debtorId'] = $debtor['id'];
        try {
            $pickup = $this->pickupRepository->createDropRepo($data['form'], $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan data pickup');
        }
        // END SAVE DROP ORDER

        // SAVE ITEM
        $items = $data['items'];
        foreach ($items as $key => $answer) {
            unset($items[$key]['service']);
        }
        try {
            $items = $this->itemRepository->save($pickup, $items);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan item / barang');
        }
        // END SAVE ITEM

        // CALCULATE PRICE
        try {
            $price = $this->billRepository->calculatePrice($items, $route, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal memperkirakan biaya pengiriman');
        }

        if (!$price->success) {
            DB::rollback();
            throw new InvalidArgumentException($price->message);
        }
        // END CALCULATE PRICE

        /**
         * CREATE PICKUP PLAN
         */

        // UPDATE BRANCH ID PADA PICKUP
        try {
            $this->pickupRepository->updateBranchRepo([$pickup['id']], $data['branchId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal mengupdate branch pada pickup order');
        }

        // SAVE PICKUP PLAN
        try {
            $result = $this->pickupPlanRepository->savePickupPlanRepo([$pickup['id']], 0, $data['userId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal menyimpan pickup plan');
        }
        /**
         * END CREATE PICKUP PLAN
         */

        /**
         * CREATE POP
         */

        // CREATE POP
        //lANJUT DARI SINI
        try {
            $result = $this->popRepository->createPOPRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Gagal membuat proof of pickup');
        }

        // START CALCULATE BILL
        try {
            $route = $this->routeRepository->getRouteByPickupRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
        }

        if ($route == null) {
            throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
        }

        try {
            $promo = $this->promoRepository->getPromoByPickup($data['pickupId']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Perhitungan biaya gagal, rute pengiriman tidak ditemukan');
        }

        try {
            $items = $this->itemRepository->fetchItemByPickupRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal mendapatkan items');
        }

        $items = collect($items)->values()->all();

        try {
            $bill = $this->billRepository->calculateAndSavePrice($items, $route, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menghitung total biaya');
        }

        if ($bill->success == false) {
            throw new InvalidArgumentException($bill->message);
        }

        $cost = [
            'pickupId' => $data['pickupId'],
            'amount' => $bill->total_price
        ];
        try {
            $this->costRepository->saveCostRepo($cost);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Log::error($e);
            throw new InvalidArgumentException('Perhitungan biaya gagal, Gagal menyimpan total biaya');
        }
        // END CALCULATE BILL
        //SELESAI DARI SINI
        /**
         * END CREATE POP
         */

        DB::commit();
        $result = (object)[
            'items' => $items,
            'route' => $route,
            'pickup' => $pickup,
            'promo' => $promo,
            'price' => $price
        ];
        return $result;
    }
}
