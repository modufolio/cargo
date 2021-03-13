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
    protected $proofOfPickupRepository;

    public function __construct(PickupRepository $pickupRepository,
        ItemRepository $itemRepository,
        BillRepository $billRepository,
        PromoRepository $promoRepository,
        RouteRepository $routeRepository,
        AddressRepository $addressRepository,
        ProofOfPickupRepository $proofOfPickupRepository
    )
    {
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
        $this->billRepository = $billRepository;
        $this->promoRepository = $promoRepository;
        $this->routeRepository = $routeRepository;
        $this->addressRepository = $addressRepository;
        $this->popRepository = $proofOfPickupRepository;
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
            throw new InvalidArgumentException($e->getMessage());
        }

        // PROMO
        if ($data['promoId'] !== null) {
            try {
                $promo = $this->promoRepository->getById($data['promoId']);
            } catch (Exception $e) {
                DB::rollback();
                Log::info($e->getMessage());
                throw new InvalidArgumentException($e->getMessage());
            }

            if ($promo !== false) {
                if ($promo['user_id'] !== $data['userId']) {
                    DB::rollback();
                    throw new InvalidArgumentException('Promo tidak dapat digunakan');
                }
            }
        } else {
            $promo = null;
        }
        // END PROMO

        // GET ROUTE
        try {
            $route = $this->routeRepository->getRouteRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Rute pengiriman tidak ditemukan');
        }

        if (!$route) {
            DB::rollback();
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }
        // END GET ROUTE

        // SAVE PICKUP
        try {
            $pickup = $this->pickupRepository->createPickupRepo($data, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan data pickup');
        }
        // END SAVE PICKUP

        // SAVE ITEM
        try {
            $items = $this->itemRepository->save($pickup, $data['items']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan item / barang');
        }
        // END SAVE ITEM

        // CALCULATE PRICE
        try {
            $price = $this->billRepository->calculatePrice($items, $route, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
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
}
