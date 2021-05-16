<?php

namespace App\Repositories;

use App\Models\ShipmentPlan;
use App\Models\Pickup;
use Carbon\Carbon;
use InvalidArgumentException;
use Haruncpi\LaravelIdGenerator\IdGenerator;
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
        $config = [
            'table' => 'shipment_plans',
            'length' => 13,
            'field' => 'number',
            'prefix' => 'SP'.Carbon::now('Asia/Jakarta')->format('ymd'),
            'reset_on_prefix_change' => true
        ];
        $shipmentPlan = new $this->shipmentPlan;
        $shipmentPlan->status = 'applied'; // applied, cancelled, draft
        $shipmentPlan->vehicle_id = $vehicleId;
        $shipmentPlan->created_by = $userId;
        $shipmentPlan->updated_by = $userId;
        $shipmentPlan->number = IdGenerator::generate($config);
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
        $shipmentPlan->updated_by = $data['userId'];
        $shipmentPlan->save();
        return $shipmentPlan;
    }

    /**
     * get shipment plan driver
     */
    public function getDriverShipmentPlanListRepo($data = [])
    {
        $userId = $data['userId'];
        $result = $this->shipmentPlan
            ->with(['pickups' => function($q) {
                $q->where('is_transit', false);
            }])
            ->whereHas('vehicle', function($q) use ($userId) {
                $q->whereHas('driver', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })->get();
        return $result;
    }

    /**
     * get po in shipment plan driver
     */
    public function getPickupOrderDriverShipmentPlanListRepo($data = [])
    {
        $userId = $data['userId'];
        $shipmentPlanId = $data['shipmentPlanId'];
        $filter = $data['filter'];
        $pickup = $this->pickup
            ->whereNotNull('pickup_plan_id')
            ->where('shipment_plan_id', $shipmentPlanId)
            ->where('is_transit', false)
            ->with(['receiver','proofOfDelivery'])
            ->whereHas('shipmentPlan', function ($q) use ($userId) {
                $q->whereHas('vehicle', function($q) use ($userId) {
                    $q->whereHas('driver', function($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
                });
            });
        if ($filter) {
            $pickup = $pickup->where(function($q) use ($filter) {
                $q->whereHas('receiver', function($q) use ($filter) {
                    $q->where('street' , 'ilike', '%'.$filter.'%')
                        ->orWhere('province', 'ilike', '%'.$filter.'%')
                        ->orWhere('name', 'ilike', '%'.$filter.'%')
                        ->orWhere('district', 'ilike', '%'.$filter.'%')
                        ->orWhere('village', 'ilike', '%'.$filter.'%')
                        ->orWhere('postal_code', 'ilike', '%'.$filter.'%')
                        ->orWhere('city', 'ilike', '%'.$filter.'%');
                })->orWhere('number', 'ilike', '%'.$filter.'%')->orWhere('name', 'ilike', '%'.$filter.'%');
            });
        }
        $result = $pickup->paginate(10);
        return $result;
    }

    /**
     * get dashboard shipment plan repo
     */
    public function getDashboardDriverRepo($shipmentPlanId)
    {
        $shipmentPlanPickup = $this->pickup->where('shipment_plan_id', $shipmentPlanId);
        $totalPickup = $shipmentPlanPickup->count();
        $capacity = $shipmentPlanPickup->with('items')->get()->pluck('items');
        $items = collect($capacity)->flatten()->toArray();
        $volume = array_sum(array_column($items, 'volume'));
        $weight = array_sum(array_column($items, 'weight'));
        $result = [
            'volume' => $volume,
            'weight' => $weight,
            'totalOrder' => $totalPickup
        ];
        return $result;
    }
}
