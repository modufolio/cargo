<?php

namespace App\Repositories;

use App\Models\ShipmentPlan;
use App\Models\Pickup;
use Carbon\Carbon;
use InvalidArgumentException;

class ShipmentPlanRepository
{
    protected $shipmentPlan;
    protected $pickup;

    public function __construct(ShipmentPlan $shipmentPlan, Pickup $pickup)
    {
        $this->shipmentPlan = $shipmentPlan;
        $this->pickup = $pickup;
    }

    /**
     * save shipment plan
     *
     * @param int $pickupId
     * @param int $vehicleId
     * @param int $userId
     * @return ShipmentPlan
     */
    public function saveShipmentPlanRepo($pickupId, $vehicleId, $userId)
    {
        $shipmentPlan = new $this->shipmentPlan;
        $shipmentPlan->status = 'applied'; // applied, cancelled, draft
        $shipmentPlan->vehicle_id = $vehicleId;
        $shipmentPlan->created_by = $userId;
        $shipmentPlan->updated_by = $userId;
        $shipmentPlan->save();
        foreach ($pickupId as $key => $value) {
            // $this->pickup->where('id', $value)->update(['pickup_plan_id' => $shipmentPlan->id]);
            $pickup = $this->pickup->find($value);
            $pickup->shipmentPlan()->associate($shipmentPlan);
            $pickup->save();
        }
        $shipmentPlan->fresh();
        return $shipmentPlan;
    }

    /**
     * delete pickup order on shipment plan
     *
     * @param array $data
     */
    public function deletePoRepo($data)
    {
        $shipmentPlan = $this->shipmentPlan->find($data['shipmentPlanId'])->pickups;
        if (count($shipmentPlan) <= 1) {
            throw new InvalidArgumentException('Maaf anda tidak bisa menghapus pickup order ini');
        }
        $shipmentPlan = $shipmentPlan->where('id', $data['pickupId'])->values();
        if (count($shipmentPlan) == 1) {
            $pickup = $this->pickup->where('id', $data['pickupId'])->where('shipment_plan_id', $data['shipmentPlanId'])->update([
                'shipment_plan_id' => null
            ]);
            return $pickup;
        }
        throw new InvalidArgumentException('Pickup order tidak ditemukan');
    }

    /**
     * add pickup order on shipment plan
     *
     * @param array $data
     */
    public function addPoRepo($data)
    {
        $shipmentPlan = $this->shipmentPlan->find($data['shipmentPlanId']);
        if (!$shipmentPlan) {
            throw new InvalidArgumentException('Maaf shipment plan tidak ditemukan');
        }
        $result = [];
        foreach ($data['pickupId'] as $key => $value) {
            $pickup = $this->pickup->find($value);
            $pickup->shipmentPlan()->associate($shipmentPlan);
            $pickup->save();
            $result[] = $pickup;
            // $this->pickup->where('id', $value)->update(['pickup_plan_id' => $shipmentPlan->id]);
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
        $pickupPlan = $this->shipmentPlan->find($data['pickupPlanId']);
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
     * cancel shipment plan
     *
     * @param array $data
     */
    public function cancelShipmentPlanRepo($data = [])
    {
        $shipmentPlan = $this->shipmentPlan->find($data['shipmentPlanId']);
        if (!$shipmentPlan) {
            throw new InvalidArgumentException('Maaf, shipment plan tidak ditemukan');
        }
        $shipmentPlan->status = 'canceled';
        $shipmentPlan->save();
        return $shipmentPlan;
    }
}