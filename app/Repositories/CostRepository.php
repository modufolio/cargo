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
     * Get cost by name
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
}
