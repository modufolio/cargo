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
     * Calculate price
     *
     * @param $unitTotal
     * @return mixed
     */
    public function calculatePrice($items, $route)
    {
        $result = $data = [];
        foreach ($items as $key => $value) {
            $unit           = $this->unit->where('id', $value['unit_id'])->select('name','price')->first();
            $service        = $this->service->where('id', $value['service_id'])->select('name','price')->first();
            $servicePrice   = $service['price'] ?? 0;
            if ($value['unit_total'] >= intval($route['minimum_weight'])) {
                $data['success']    = true;
                $data['price']      = ($value['unit_total'] * intval($unit['price'])) + $servicePrice;
            } else  {
                $data['success']    = false;
                $data['price']      = 0;
            }
            $data['name']       = $value['name'];
            $data['unit']       = $unit;
            $data['unit_total'] = $value['unit_total'];
            $data['service']    = $service ?? null;
            $result[] = $data;
        }
        $calculation = $result;
        $result = (object)[
            'per_item'      => $calculation,
            'total_price'   => array_sum(array_column($calculation, 'price')) + intval($route['price'])
        ];
        return $result;
    }
}
