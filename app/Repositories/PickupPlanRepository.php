<?php

namespace App\Repositories;

use App\Models\PickupPlan;
use App\Models\Pickup;
use Carbon\Carbon;
use InvalidArgumentException;
use Haruncpi\LaravelIdGenerator\IdGenerator;

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
        $config = [
            'table' => 'pickup_plans',
            'length' => 13,
            'field' => 'number',
            'prefix' => 'PP'.Carbon::now('Asia/Jakarta')->format('ymd'),
            'reset_on_prefix_change' => true
        ];
        $pickupPlan = new $this->pickupPlan;
        $pickupPlan->status = 'applied'; // applied, cancelled, draft
        $pickupPlan->vehicle_id = $vehicleId;
        $pickupPlan->created_by = $userId;
        $pickupPlan->number = IdGenerator::generate($config);
        $pickupPlan->save();

        foreach ($pickupId as $key => $value) {
            // $this->pickup->where('id', $value)->update(['pickup_plan_id' => $pickupPlan->id]);
            $pickup = $this->pickup->find($value);
            $pickup->pickupPlan()->associate($pickupPlan);
            $pickup->save();
        }
        $pickupPlan->fresh();
        return $pickupPlan;
    }

    /**
     * delete pickup order on pickup plan
     *
     * @param array $data
     */
    public function deletePoRepo($data)
    {
        $pickupPlan = $this->pickupPlan->find($data['pickupPlanId'])->pickups;
        if (count($pickupPlan) <= 1) {
            throw new InvalidArgumentException('Maaf anda tidak bisa menghapus pickup order ini');
        }
        $pickupPlan = $pickupPlan->where('id', $data['pickupId'])->values();
        if (count($pickupPlan) == 1) {
            $pickup = $this->pickup->where('id', $data['pickupId'])->where('pickup_plan_id', $data['pickupPlanId'])->update([
                'pickup_plan_id' => null
            ]);
            return $pickup;
        }
        throw new InvalidArgumentException('Pickup order tidak ditemukan');
    }

    /**
     * add pickup order on pickup plan
     *
     * @param array $data
     */
    public function addPoRepo($data)
    {
        $pickupPlan = $this->pickupPlan->find($data['pickupPlanId']);
        if (!$pickupPlan) {
            throw new InvalidArgumentException('Maaf pickup plan tidak ditemukan');
        }
        $result = [];
        foreach ($data['pickupId'] as $key => $value) {
            $pickup = $this->pickup->find($value);
            $pickup->pickupPlan()->associate($pickupPlan);
            $pickup->save();
            $result[] = $pickup;
            // $this->pickup->where('id', $value)->update(['pickup_plan_id' => $pickupPlan->id]);
        }
        return $result;
    }

    /**
     * delete pickup plan
     *
     * @param array $data
     */
    public function deletePickupPlanRepo($data = [])
    {
        $pickupPlan = $this->pickupPlan->find($data['pickupPlanId']);
        if (!$pickupPlan) {
            throw new InvalidArgumentException('Maaf, pickup plan tidak ditemukan');
        }
        $pickup = $this->pickup->where('pickup_plan_id', $data['pickupPlanId'])->update([
            'pickup_plan_id' => null
        ]);
        if ($pickup) {
            $pickupPlan->deleted_by = $data['userId'];
            $pickupPlan->save();
            $pickupPlan->delete();
            return $pickupPlan;
        }
        throw new InvalidArgumentException('Maaf, pickup order yang ada di pickup plan tidak bisa dihapus');
    }

    /**
     * cancel pickup plan
     *
     * @param array $data
     */
    public function cancelPickupPlanRepo($data = [])
    {
        $pickupPlan = $this->pickupPlan->find($data['pickupPlanId']);
        if (!$pickupPlan) {
            throw new InvalidArgumentException('Maaf, pickup plan tidak ditemukan');
        }
        $pickupPlan->status = 'canceled';
        $pickupPlan->save();
        return $pickupPlan;
    }
}
