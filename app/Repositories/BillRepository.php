<?php

namespace App\Repositories;

use App\Models\Bill;
use App\Models\Pickup;
use App\Models\Service;
use App\Models\Unit;
use App\Models\Item;
use Carbon\Carbon;

class BillRepository
{
    protected $bill;
    protected $pickup;
    protected $unit;
    protected $service;

    public function __construct(Bill $bill, Pickup $pickup, Unit $unit, Service $service, Item $item)
    {
        $this->bill = $bill;
        $this->pickup = $pickup;
        $this->unit = $unit;
        $this->service = $service;
        $this->item = $item;
    }

    /**
     * Get all bill.
     *
     * @return Bill $bill
     */
    public function getAll()
    {
        return $this->bill->get();
    }

    /**
     * Get bill by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->bill->where('id', $id)->get();
    }

    /**
     * Get bill by pickup id
     *
     * @param $pickupId
     * @return mixed
     */
    public function getByPickupId($pickupId)
    {
        return $this->pickup->find($pickupId)->bill()->get();
    }

    /**
     * @param array $items
     * @param array $route
     * @param array $promo
     * @param boolean $savePrice
     *
     * @return object
     */
    public function calculatePriceRepo($items, $route, $promo, $savePrice)
    {
        $result = $data = [];
        $totalWeight = array_sum(array_column($items, 'weight'));
        if ($totalWeight >= intval($route['minimum_weight'])) {
            foreach ($items as $key => $value) {
                $service                = $this->service->where('id', $value['service_id'])->select('name','price')->first();
                $servicePrice           = $service['price'] ?? 0;
                $priceItem              = $this->getPricePerItem($value['type'], $value['weight'], $route, $servicePrice);
                $data['price']          = $priceItem['price'];
                $data['clear_price']    = $priceItem['clear_price'];
                $data['service_price']  = $servicePrice;
                $data['name']           = $value['name'];
                $data['weight']         = $value['weight'];
                $data['type']           = $value['type'];
                $data['volume']         = $value['volume'];
                $data['unit']           = $value['unit'] ?? 'buah';
                $data['unit_count']     = $value['unit_count'];
                $data['service']        = $service ?? null;
                $data['service_id']     = $service['id'] ?? null;
                $data['id']             = $value['id'] ?? null;
                $itemData[] = $data;
            }
            if ($savePrice) {
                foreach ($itemData as $key => $value) {
                    $this->item->where('id', $value['id'])->update([
                        'price' => $value['price'],
                        'clear_price' => $value['clear_price'],
                        'service_price' => $value['service_price']
                    ]);
                }
            }
            $total = array_sum(array_column($itemData, 'price'));
            $totalClearPrice = array_sum(array_column($itemData, 'clear_price'));
            $totalService = array_sum(array_column($itemData, 'service_price'));
            $finalTotal = $this->addingPromo($total, $promo);
            $result = (object)[
                'success'                   => true,
                'total_weight'              => $totalWeight,
                'items'                     => $itemData,
                'promo'                     => $promo,
                'total_price'               => $finalTotal['total'], // total dengan potongan diskon dan tambahan biaya service
                'total_service'             => $totalService, // total service
                'total_discount'            => $finalTotal['discount'], // total diskon
                'total_clear_price'         => $totalClearPrice, // total tanpa diskon, dan service, hanya biaya barang
                'total_price_with_service'  => $total // total tanpa diskon, dan service, hanya biaya barang
            ];
        } else {
            $result = (object)[
                'success' => false,
                'message' => 'Total berat barang tidak memenuhi minimum persyaratan pengiriman'
            ];
        }
        return $result;
    }

    public function addingPromo($total, $promo) : array
    {
        $total = intval($total);
        if ($promo) {
            $minValue = intval($promo['min_value']);
            $promoDiscount = intval($promo['discount']);
            $promoDiscountMax = intval($promo['discount_max']);
            /**
             * jika harga total biaya lebih tinggi daripada ketentuan minimum biaya untuk mendapatkan promo
             */
            if ($total >= $minValue) {
                /** perhitungan diskon (step 1) */
                $discount = ($total * $promoDiscount) / 100;
                /** jika jumlah harga diskon lebih besar daripada jumlah ketentuan maksimal diskon */
                if (intval($discount) >= $promoDiscountMax) {
                    /** hitung total biaya dikurang diskon menggunakan ketentuan maksimal diskon */
                    $totalWithDiscount = $total - $promoDiscountMax;
                    $total = ['total' => $totalWithDiscount, 'discount' => $promoDiscountMax];
                } else {
                    /** hitung total biaya dikurang diskon menggunakan diskon yang didapat dari perhitungan diskon (step 1) */
                    $totalWithDiscount = $total - intval($discount);
                    $total = ['total' => $totalWithDiscount, 'discount' => $discount];
                }
            } else {
                /**
                 * jika harga total biaya lebih kecil daripada ketentuan minimum biaya untuk mendapatkan promo,
                 * maka hapus diskon
                 */
                $total = [
                    'total' => $total,
                    'discount' => 0
                ];
            }
        } else {
            /**
             * jika tidak memakai promo,
             * maka hapus diskon
             */
            $total = [
                'total' => $total,
                'discount' => 0
            ];
        }
        return $total;
    }

    public function getPricePerItem($type, $totalWeight, $route, $servicePrice)
    {
        if ($type == 'barang') {
            $price = (intval($totalWeight) * intval($route['price'])) + $servicePrice;
            $clearPrice = (intval($totalWeight) * intval($route['price']));
        }
        if ($type == 'mobil') {
            $price = (intval($totalWeight) * intval($route['price_car'])) + $servicePrice;
            $clearPrice = (intval($totalWeight) * intval($route['price_car']));
        }
        if ($type == 'motor') {
            $price = (intval($totalWeight) * intval($route['price_motorcycle'])) + $servicePrice;
            $clearPrice = (intval($totalWeight) * intval($route['price_motorcycle']));
        }
        return [
            'price' => $price,
            'clear_price' => $clearPrice
        ];
    }

    /**
     * @param array $items
     * @param array $route
     * @param array $promo
     * DEPRECATED
     * @return object
     */
    // public function calculatePriceWithoutPromoRepo($items, $route)
    // {
    //     $result = $data = [];
    //     $totalWeight = array_sum(array_column($items, 'weight'));
    //     $service = collect($this->service->select('id','name','price')->get());
    //     if ($totalWeight >= intval($route['minimum_weight'])) {
    //         foreach ($items as $key => $value) {
    //             $service            = $service->where('id', $value['service_id'])->first();
    //             $servicePrice       = $service['price'] ?? 0;
    //             $data['price']      = ($value['weight'] * intval($route['price'])) + $servicePrice;
    //             $data['name']       = $value['name'];
    //             $data['weight']     = $value['weight'];
    //             $data['type']       = $value['type'];
    //             $data['volume']     = $value['volume'];
    //             $data['unit_count'] = $value['unit_count'];
    //             $data['service']    = $service ?? null;
    //             $data['service_id'] = $value['service_id'];
    //             $data['unit']       = $value['unit'] ?? 'buah';
    //             $data['service_price'] = $servicePrice;
    //             $itemData[]         = $data;
    //         }
    //         $total = array_sum(array_column($itemData, 'price'));
    //         $totalService = array_sum(array_column($itemData, 'service_price'));
    //         $result = (object)[
    //             'success'       => true,
    //             'total_weight'  => $totalWeight,
    //             'items'         => $itemData,
    //             'total_service' => $totalService,
    //             'total_price'   => $total,
    //             'total_discount' => 0,
    //             'total_clear_price' => $total
    //         ];
    //     } else {
    //         $result = (object)[
    //             'success' => false,
    //             'message' => 'Total berat barang tidak memenuhi minimum persyaratan pengiriman'
    //         ];
    //     }
    //     return $result;
    // }

    /**
     * @param array $items
     * @param array $route
     * @param array $promo
     * DEPRECATED
     * @return object
     */
    // public function calculateAndSavePrice($items, $route, $promo)
    // {
    //     $result = $data = [];
    //     $totalWeight = array_sum(array_column($items, 'weight'));
    //     if ($totalWeight >= intval($route['minimum_weight'])) {
    //         foreach ($items as $key => $value) {
    //             $service            = $this->service->where('id', $value['service_id'])->select('name','price')->first();
    //             $servicePrice       = $service['price'] ?? 0;
    //             $price              = $this->getPricePerItem($value['type'], $value['weight'], $route, $servicePrice);
    //             $data['price']      = $price;
    //             $data['service_price'] = $servicePrice;
    //             $data['id']         = $value['id'];
    //             $itemData[]         = $data;
    //         }
    //         // SAVE / UPDATE PRICE
    //         foreach ($itemData as $key => $value) {
    //             $this->item->where('id', $value['id'])->update(['price' => $value['price']]);
    //         }
    //         $total = array_sum(array_column($itemData, 'price'));
    //         $totalService = array_sum(array_column($itemData, 'service_price'));
    //         $finalTotal = $this->addingPromo($total, $promo);
    //         $result = (object)[
    //             'success'       => true,
    //             'total_weight'  => $totalWeight,
    //             'items'         => $itemData,
    //             'promo'         => $promo,
    //             'total_service' => $totalService,
    //             'total_price'   => $finalTotal['total'],
    //             'total_discount' => $finalTotal['discount'],
    //             'total_without_discount' => $total
    //         ];
    //     } else {
    //         $result = (object)[
    //             'success' => false,
    //             'message' => 'Total berat barang tidak memenuhi minimum persyaratan pengiriman'
    //         ];
    //     }
    //     return $result;
    // }
}
