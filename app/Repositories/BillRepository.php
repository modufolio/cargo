<?php

namespace App\Repositories;

use App\Models\Bill;
use App\Models\Pickup;
use App\Models\Service;
use App\Models\Unit;
use Carbon\Carbon;

class BillRepository
{
    protected $bill;
    protected $pickup;
    protected $unit;
    protected $service;

    public function __construct(Bill $bill, Pickup $pickup, Unit $unit, Service $service)
    {
        $this->bill = $bill;
        $this->pickup = $pickup;
        $this->unit = $unit;
        $this->service = $service;
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
     *
     * @return object
     */
    public function calculatePrice($items, $route, $promo)
    {
        $result = $data = [];
        $totalWeight = array_sum(array_column($items, 'weight'));
        if ($totalWeight >= intval($route['minimum_weight'])) {
            foreach ($items as $key => $value) {
                // $unit               = $this->unit->where('id', $value['unit_id'])->select('name','price')->first();
                $service            = $this->service->where('id', $value['service_id'])->select('name','price')->first();
                $servicePrice       = $service['price'] ?? 0;
                $price              = $this->getPricePerItem($value['type'], $totalWeight, $route, $servicePrice);
                $data['price']      = $price;
                $data['name']       = $value['name'];
                // $data['unit']       = $unit;
                $data['weight']     = $value['weight'];
                $data['type']       = $value['type'];
                $data['volume']     = $value['volume'];
                $data['service']    = $service ?? null;
                $itemData[] = $data;
            }
            $total = array_sum(array_column($itemData, 'price'));
            $finalTotal = $this->addingPromo($total, $promo);
            $result = (object)[
                'success'       => true,
                'total_weight'  => $totalWeight,
                'items'         => $itemData,
                'total_price'   => $finalTotal
            ];
        } else {
            $result = (object)[
                'success' => false,
                'message' => 'Total berat barang tidak memenuhi minimum persyaratan pengiriman'
            ];
        }
        return $result;
    }

    public function addingPromo($total, $promo)
    {
        $total = intval($total);
        if ($promo) {
            $minValue = intval($promo['min_value']);
            $promoDiscount = intval($promo['discount']);
            $promoDiscountMax = intval($promo['discount_max']);
            if ($total >= $minValue) {
                $discount = ($total * $promoDiscount) / 100;
                if (intval($discount) >= $promoDiscountMax) {
                    $total = $total - $promoDiscountMax;
                } else {
                    $total = $total - intval($discount);
                }
            }
        }
        return $total;
    }

    public function getPricePerItem($type, $totalWeight, $route, $servicePrice)
    {
        if ($type == 'barang') {
            $price = (intval($totalWeight) * intval($route['price'])) + $servicePrice;
        }
        if ($type == 'mobil') {
            $price = (intval($totalWeight) * intval($route['price_car'])) + $servicePrice;
        }
        if ($type == 'motor') {
            $price = (intval($totalWeight) * intval($route['price_motorcycle'])) + $servicePrice;
        }
        return $price;
    }

    /**
     * @param array $items
     * @param array $route
     * @param array $promo
     *
     * @return object
     */
    public function calculatePriceWithoutPromo($items, $route)
    {
        $result = $data = [];
        $totalWeight = array_sum(array_column($items, 'unit_total'));
        if ($totalWeight >= intval($route['minimum_weight'])) {
            foreach ($items as $key => $value) {
                $unit               = $this->unit->where('id', $value['unit_id'])->select('price','slug')->first();
                $service            = $this->service->where('id', $value['service_id'])->select('name','price')->first();
                $servicePrice       = $service['price'] ?? 0;
                $data['price']      = ($value['unit_total'] * intval($route['price'])) + $servicePrice;
                $data['name']       = $value['name'];
                $data['unit']       = $unit;
                $data['unit_total'] = $value['unit_total'];
                $data['unit_count'] = $value['unit_count'];
                $data['service']    = $service ?? null;
                $itemData[] = $data;
            }
            $total = array_sum(array_column($itemData, 'price'));
            $result = (object)[
                'success'       => true,
                'total_weight'  => $totalWeight,
                'items'         => $itemData,
                'total_price'   => $total
            ];
        } else {
            $result = (object)[
                'success' => false,
                'message' => 'Total berat barang tidak memenuhi minimum persyaratan pengiriman'
            ];
        }
        return $result;
    }

    /**
     * @param array $items
     * @param array $route
     * @param array $promo
     *
     * @return object
     */
    public function calculateAndSavePrice($items, $route, $promo)
    {
        $result = $data = [];
        $totalWeight = array_sum(array_column($items, 'weight'));
        if ($totalWeight >= intval($route['minimum_weight'])) {
            foreach ($items as $key => $value) {
                $service            = $this->service->where('id', $value['service_id'])->select('name','price')->first();
                $servicePrice       = $service['price'] ?? 0;
                $price              = $this->getPricePerItem($value['type'], $totalWeight, $route, $servicePrice);
                $data['price']      = $price;
                $data['id']         = $value['id'];
                $itemData[]         = $data;
                $this->item->where('id', $value['id'])->update(['price', $price]);
            }
            $total = array_sum(array_column($itemData, 'price'));
            $finalTotal = $this->addingPromo($total, $promo);
            $result = (object)[
                'success'       => true,
                'total_weight'  => $totalWeight,
                'items'         => $itemData,
                'total_price'   => $finalTotal
            ];
        } else {
            $result = (object)[
                'success' => false,
                'message' => 'Total berat barang tidak memenuhi minimum persyaratan pengiriman'
            ];
        }
        return $result;
    }
}
