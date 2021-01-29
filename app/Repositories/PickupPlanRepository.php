<?php

namespace App\Repositories;

use App\Models\PickupPlan;
use App\Models\Pickup;
use Carbon\Carbon;

class PickupPlanRepository
{
    protected $pickupPlan;
    protected $pickup;

    public function __construct(PickupPlan $pickupPlan, Pickup $pickup)
    {
        $this->pickupPlan = $pickupPlan;
        $this->pickup = $pickup;
    }

    /**
     * save pickup plan
     *
     * @param int $pickupId
     * @param int $vehicleId
     * @param int $userId
     * @return PickupPlan
     */
    public function savePickupPlanRepo($pickupId, $vehicleId, $userId)
    {
        $pickupPlan = new $this->pickupPlan;
        $pickupPlan->status = 'pending';
        $pickupPlan->vehicle_id = $vehicleId;
        $pickupPlan->created_by = $userId;
        $pickupPlan->save();

        foreach ($pickupId as $key => $value) {
            $this->pickup->where('id', $value)->update(['pickup_plan_id' => $pickupPlan->id]);
        }
        $pickupPlan->fresh();
        return $pickupPlan;
    }
}
