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
        $cost->clear_amount = $data['clearAmount'];
        $cost->discount = $data['discount'];
        $cost->service = $data['service'];
        $cost->save();
        return $cost;
    }

    /**
     * update payment method
     */
    public function updatePaymentMethod($data = [])
    {
        $cost = $this->cost->find($data['id']);
        if (strtolower($data['method']) == 'tempo') {
            $cost->due_date = $data['dueDate'];
        }
        $cost->method = $data['method'];
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
            [
                'pickup_id' => $data['pickupId']
            ],
            [
                'amount' => $data['amount'],
                'clear_amount' => $data['clearAmount'],
                'discount' => $data['discount'],
                'service' => $data['service']
            ]
        );
        return $cost;
    }

    /**
     * update cost
     *
     * @param array $data
     * @return Cost
     */
    public function editCostRepo($data = [])
    {
        return $this->cost->where('pickup_id', $data['pickupId'])->update([
            'amount' => $data['amount'],
            'due_date' => $data['dueDate'],
            'method' => $data['method'],
            'clear_amount' => $data['clearAmount'],
            'discount' => $data['discount'],
            'service' => $data['service']
        ]);
    }
}
