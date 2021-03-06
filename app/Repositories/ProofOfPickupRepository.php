<?php

namespace App\Repositories;

use App\Models\ProofOfPickup;
use App\Models\Pickup;
use Carbon\Carbon;
use DB;
use InvalidArgumentException;
use Exception;

class ProofOfPickupRepository
{
    protected $pop;
    protected $pickup;

    public function __construct(ProofOfPickup $pop, Pickup $pickup)
    {
        $this->pop = $pop;
        $this->pickup = $pickup;
    }

    /**
     * create POP
     *
     * @param array $data
     * @return ProofOfPickup
     */
    public function createPOPRepo($data = [])
    {
        DB::beginTransaction();
        try {
            $proof = new $this->pop;
            $proof->status = 'applied';
            $proof->pickup_id = $data['pickupId'];
            $proof->driver_pick = $data['driverPick'];
            $proof->notes = $data['notes'];
            $proof->created_by = $data['userId'];
            $proof->save();

        } catch (Exception $e) {
            DB::rollback();
            throw new InvalidArgumentException('Gagal menyimpan data proof of pickup');
        }

        try {
            $pickup = $this->pickup->find($data['pickupId']);
            if (!$pickup) {
                DB::rollback();
                throw new InvalidArgumentException('Pickup tidak ditemukan');
            }
            $pickup->status = $data['status'];
            $pickup->save();
        } catch (Exception $e) {
            DB::rollback();
            throw new InvalidArgumentException('Gagal, mengubah status pickup');
        }

        DB::commit();
        return [
            'pop' => $proof,
            'pickup' => $pickup
        ];
    }

    /**
     * get outstanding proof of pickup
     * @param array $data
     */
    public function getOutstandingPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $sort = $data['sort'];
        $customer = $data['customer'];
        $general = $data['general'];
        $pickupOrderNo = $data['pickupOrderNo'];
        $requestPickupDate = $data['requestPickupDate'];
        $pickupPlanNo = $data['pickupPlanNo'];

        $pickup = $this->pickup->where('status', 'request')->whereNotNull('pickup_plan_id');

        if (empty($perPage)) {
            $perPage = 10;
        }

        if (!empty($sort['field'])) {
            $order = $sort['order'];
            if ($order == 'ascend') {
                $order = 'asc';
            } else if ($order == 'descend') {
                $order = 'desc';
            } else {
                $order = 'desc';
            }
            switch ($sort['field']) {
                case 'name':
                    $pickup = $pickup->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'pickup_plan_id':
                    $pickup = $pickup->sortable([
                        'pickup_plan_id' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickup = $pickup->sortable([
                        'picktime' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($customer)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$customer.'%');
        }

        if (!empty($pickupOrderNo)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$pickupOrderNo.'%');
        }

        if (!empty($requestPickupDate)) {
            $pickup = $pickup->whereDate('picktime', date($requestPickupDate));
        }

        if (!empty($pickupPlanNo)) {
            $pickup = $pickup->where('pickup_plan_id', 'ilike', '%'.$pickupPlanNo.'%');
        }

        if (!empty($general)) {
            $pickup = $pickup
                ->where('name', 'ilike', '%'.$general.'%')
                ->orWhere('id', 'ilike', '%'.$general.'%')
                ->orWhere('pickup_plan_id', 'ilike', '%'.$general.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }
}
