<?php
namespace App\Services;

use App\Models\Pickup;
use App\Repositories\PickupRepository;
use App\Repositories\ItemRepository;
use App\Repositories\BillRepository;
use App\Repositories\PromoRepository;
use App\Repositories\RouteRepository;
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

    public function __construct(PickupRepository $pickupRepository,
        ItemRepository $itemRepository,
        BillRepository $billRepository,
        PromoRepository $promoRepository,
        RouteRepository $routeRepository)
    {
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
        $this->billRepository = $billRepository;
        $this->promoRepository = $promoRepository;
        $this->routeRepository = $routeRepository;
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
            'promoCode'             => 'bail|nullable|max:19',
            'name'                  => 'bail|required|max:255',
            'phone'                 => 'bail|required|max:14',
            'addressSender'         => 'bail|required|max:500',
            'addressReceiver'       => 'bail|required|max:500',
            'addressBilling'        => 'bail|required|max:500',
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

        try {
            $promo = $this->promoRepository->getByCode($data['promoCode']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat permintaan pickup (code: 5001)');
        }

        try {
            $pickup = $this->pickupRepository->save($data, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat permintaan pickup (code: 5002)');
        }

        try {
            $items = $this->itemRepository->save($pickup, $data['items']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat permintaan pickup (code: 5003)');
        }

        try {
            $route = $this->routeRepository->getByFleetOriginDestination($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat permintaan pickup (code: 5004)');
        }

        if (!$route) {
            DB::rollback();
            throw new InvalidArgumentException('Mohon maaf, untuk saat ini kota tujuan yang Anda mau belum masuk kedalam jangkauan kami');
        }

        try {
            $price = $this->billRepository->calculatePrice($items, $route, $promo);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat permintaan pickup (code: 5005)');
        }

        if (!$price->success) {
            DB::rollback();
            throw new InvalidArgumentException($price->message);
        }

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
