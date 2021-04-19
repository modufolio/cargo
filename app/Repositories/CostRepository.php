<?php

namespace App\Repositories;

use App\Models\Cost;
use Carbon\Carbon;
use InvalidArgumentException;

class CostRepository
{
    protected $cost;

    public function __construct(Cost $cost)
    {
        $this->cost = $cost;
    }

    /**
     * save cost
     *
     * @param array $data
     * @return Cost
     */
    public function saveCostRepo($data = [])
    {
        $cost = new $this->cost;
        $cost->pickup_id = $data['pickupId'];
        $cost->amount = $data['amount'];
        $cost->save();
        return $cost;
    }

    /**
     * update amount cost by pickup
     *
     * @param array $data
     * @return Cost
     */
    public function updateCostByPickupRepo($data)
    {
        $cost = $this->cost->updateOrCreate(
            ['pickup_id' => $data['pickupId']],
            ['amount' => $data['amount']]
        );
        return $cost;
    }
}
