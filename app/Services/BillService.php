<?php
namespace App\Services;

// MODELS
use App\Models\User;

// REPO
use App\Repositories\BillRepository;
use App\Repositories\RouteRepository;

// OTHER
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class BillService {

    protected $billRepository;
    protected $routeRepository;

    public function __construct(BillRepository $billRepository, RouteRepository $routeRepository)
    {
        $this->billRepository = $billRepository;
        $this->routeRepository = $routeRepository;
    }

    /**
     * Get all bill.
     *
     * @return String
     */
    public function getAll()
    {
        return $this->billRepository->getAll();
    }

    /**
     * Calculate price.
     *
     * @return mixed
     */
    public function calculatePriceService($items = [], $route, $promo)
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Item tidak ditemukan');
        }

        if (empty($route)) {
            throw new InvalidArgumentException('Rute tidak masuk dalam jangkauan');
        }

        $validator = Validator::make($route->toArray(), [
            'origin'                => 'bail|required',
            'destination_district'  => 'bail|required',
            'destination_city'      => 'bail|required',
            'price'                 => 'bail|required|numeric',
            'price_motorcycle'      => 'bail|required|numeric',
            'price_car'             => 'bail|required|numeric',
            'minimum_weight'        => 'bail|required|numeric'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $validator = Validator::make($promo->toArray(), [
            'discount'      => 'bail|required|numeric',
            'discount_max'  => 'bail|required|numeric',
            'min_value'     => 'bail|required|numeric',
            'start_at'      => 'bail|required',
            'end_at'        => 'bail|required',
            'max_used'      => 'bail|required|numeric',
            'code'          => 'bail|required',
            'scope'         => 'bail|required'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            throw new InvalidArgumentException($validator->errors()->first());
        }

        foreach ($items as $key => $value) {
            $validator = Validator::make($value, [
                'unit'          => 'bail|required',
                'unit_count'    => 'bail|required|numeric',
                'type'          => 'bail|required',
                'name'          => 'bail|required',
                'weight'        => 'bail|required|numeric',
                'volume'        => 'bail|required|numeric',
                'service_id'    => 'bail|nullable|present'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }
        }

        /**
         * hitung perkiraan biaya sementara
         */
        try {
            $result = $this->billRepository->calculatePriceRepo($items, $route, $promo, false);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal memperkirakan biaya');
        }

        if (!$result->success) {
            throw new InvalidArgumentException($result->message);
        }

        return $result;
    }

    /**
     * Calculate price without promo.
     *
     * @return mixed
     */
    public function calculatePriceWithoutPromoService($items = [], $route = [])
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Item tidak ditemukan');
        }
        if (empty($route)) {
            throw new InvalidArgumentException('Rute tidak masuk dalam jangkauan');
        }

        foreach ($items as $key => $value) {
            $validator = Validator::make($value, [
                'unit'          => 'bail|required',
                'unit_count'    => 'bail|required|numeric',
                'type'          => 'bail|required',
                'name'          => 'bail|required',
                'weight'        => 'bail|required|numeric',
                'volume'        => 'bail|required|numeric',
                'service_id'    => 'bail|nullable|present'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                throw new InvalidArgumentException($validator->errors()->first());
            }
        }

        try {
            $result = $this->billRepository->calculatePriceRepo($items, $route, null, false);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal memperkirakan biaya');
        }

        if (!$result->success) {
            throw new InvalidArgumentException($result->message);
        }

        return $result;
    }
}
