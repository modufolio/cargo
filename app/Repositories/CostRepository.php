<?php

namespace App\Repositories;

use App\Models\Cost;
use App\Models\ExtraCost;
use Carbon\Carbon;
use InvalidArgumentException;

class CostRepository
{
    protected $cost;
    protected $extraCost;

    public function __construct(Cost $cost, ExtraCost $extraCost)
    {
        $this->cost = $cost;
        $this->extraCost = $extraCost;
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
        $cost->amount_with_service = $data['amountWithService'];
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
    public function updateOrCreateCostByPickupIdRepo($data)
    {
        $cost = $this->cost->updateOrCreate(
            [
                'pickup_id' => $data['pickupId']
            ],
            [
                'amount' => $data['amount'],
                'clear_amount' => $data['clearAmount'],
                'discount' => $data['discount'],
                'service' => $data['service'],
                'amount_with_service' => $data['amountWithService'],
            ]
        );
        return $cost;
    }

    /**
     * update cost by pickup
     *
     * @param array $data
     * @return Cost
     */
    public function updateCostByPickupIdRepo($data = [])
    {
        return $this->cost->where('pickup_id', $data['pickupId'])->update([
            'amount' => $data['amount'],
            'due_date' => $data['dueDate'],
            'method' => $data['method'],
            'clear_amount' => $data['clearAmount'],
            'discount' => $data['discount'],
            'service' => $data['service'],
            'amount_with_service' => $data['amountWithService'],
        ]);
    }

    /**
     * update cost repo
    */
    public function updateCostRepo($data = [])
    {
        $cost = $this->cost->find($data['id']);
        $cost->amount = $data['amount'];
        $cost->method = $data['method'];
        $cost->due_date = $data['dueDate'];
        $cost->discount = $data['discount'];
        $cost->service = $data['service'];
        $cost->clear_amount = $data['clearAmount'];
        $cost->status = ucwords($data['status']);
        $cost->notes = $data['notes'];
        $cost->amount_with_service = $data['amountWithService'];
        $cost->save();
        return $cost;
    }

    /**
     * update extra costs
     */
    public function updateExtraCostRepo($data = [])
    {
        $extraCost = $this->extraCost->find($data['id']);
        $extraCost->amount = $data['amount'];
        $extraCost->notes = $data['notes'];
        $extraCost->save();
        return $extraCost;
    }

    /**
     * save extra cost
     */
    public function saveExtraCostRepo($data = [])
    {
        $extraCost = new $this->extraCost;
        $extraCost->cost_id = $data['costId'];
        $extraCost->amount = $data['amount'];
        $extraCost->notes = $data['notes'];
        $extraCost->created_by = $data['userId'];
        $extraCost->updated_by = $data['userId'];
        $extraCost->save();
        return $extraCost;
    }

    /**
     * delete extra cost by cost id
     */
    public function deleteExtraCostByCostIdRepo($costId)
    {
        $this->extraCost->where('cost_id', $costId)->delete();
    }
}
