<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Pickup;
use App\Models\User;
use Indonesia;
use Carbon\Carbon;

class PickupRepository
{
    protected $pickup;

    public function __construct(Pickup $pickup)
    {
        $this->pickup = $pickup;
    }

    /**
     * Save Pickup Address / alamat pickup / alamat pengirim
     *
     * @param array $data
     * @param Promo $promo
     * @return Pickup
     */
    public function save($data, $promo)
    {
        $pickup = new $this->pickup;

        $pickup->fleet_id           = $data['fleetId'];
        $pickup->user_id            = $data['userId'];
        $pickup->promo_id           = $promo['id'] ?? null;
        $pickup->name               = $data['name'];
        $pickup->phone              = $data['phone'];
        $pickup->sender_id          = $data['senderId'];
        $pickup->receiver_id        = $data['receiverId'];
        $pickup->debtor_id          = $data['debtorId'];
        $pickup->notes              = $data['notes'];
        $pickup->picktime           = $data['picktime'];
        $pickup->status             = 'request';
        $pickup->save();

        return $pickup->fresh();
    }

    public function savePickupPlanRepo($data)
    {
        $pickup = new $this->pickup;

        $pickup->fleet_id           = $data['fleetId'];
        $pickup->user_id            = $data['userId'];
        $pickup->promo_id           = $promo['id'] ?? null;
        $pickup->name               = $data['name'];
        $pickup->phone              = $data['phone'];
        $pickup->sender_id          = $data['senderId'];
        $pickup->receiver_id        = $data['receiverId'];
        $pickup->debtor_id          = $data['debtorId'];
        $pickup->notes              = $data['notes'];
        $pickup->picktime           = $data['picktime'];
        $pickup->save();

        return $pickup->fresh();
    }

    /**
     * Save get pickup by userId
     *
     * @param Pickup $pickup
     */
    public function getByUserId($pickup)
    {
        return $this->user->find($id)->pickups()->get();
    }

    /**
     * get all pickup pagination
     *
     * @param Pickup $pickup
     */
    public function getAllPickupPaginate($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $sort = $data['sort'];

        $pickup = $this->pickup->with(['user','sender','receiver','debtor','fleet','promo']);

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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickup = $pickup->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
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

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($picktime)) {
            $pickup = $pickup->where('picktime', 'ilike', '%'.$picktime.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get ready to pickup pagination
     *
     * @param Pickup $pickup
     */
    public function getReadyToPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $sort = $data['sort'];

        $pickup = $this->pickup->whereNull('pickup_plan_id')->with(['user','sender','receiver','debtor','fleet','promo']);

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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickup = $pickup->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
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

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($picktime)) {
            $pickup = $pickup->where('picktime', 'ilike', '%'.$picktime.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }
}
