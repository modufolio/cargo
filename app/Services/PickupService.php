<?php
namespace App\Services;

// use App\Models\Pickup;
use App\Repositories\PickupRepository;
use App\Repositories\ItemRepository;
use App\Repositories\BillRepository;
use App\Repositories\PromoRepository;
use App\Repositories\RouteRepository;
use App\Repositories\AddressRepository;
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

    public function __construct(PickupRepository $pickupRepository,
        ItemRepository $itemRepository,
        BillRepository $billRepository,
        PromoRepository $promoRepository,
        RouteRepository $routeRepository,
        AddressRepository $addressRepository
    )
    {
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
        $this->billRepository = $billRepository;
        $this->promoRepository = $promoRepository;
        $this->routeRepository = $routeRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Validate pickup data.
     * Store to DB if there are no errors.
     *
     * @param data
     * @return mixed
     */
    public function save($data)
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
            $pickup = $this->pickupRepository->save($data, $promo);
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
     */
    public function getAllPaginate($data)
    {
        try {
            $pickup = $this->pickupRepository->getAllPickupPaginate($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal mendapatkan data pickup');
        }
        return $pickup;
    }
}
