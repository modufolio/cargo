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
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];

        $pickup = $this->pickup->with(['user','sender','receiver','debtor','fleet','promo']);

        if (empty($perPage)) {
            $perPage = 10;
        }

        if (!empty($name)) {
            $pickup = $pickup->sortable([
                'sender.name' => $name
            ]);
        }

        if (!empty($city)) {
            $pickup = $pickup->sortable([
                'sender.city' => $city
            ]);
        }

        if (!empty($district)) {
            $pickup = $pickup->sortable([
                'sender.district' => $district
            ]);
        }

        if (!empty($village)) {
            $pickup = $pickup->sortable([
                'sender.village' => $village
            ]);
        }

        if (!empty($picktime)) {
            $pickup = $pickup->sortable([
                'picktime' => $picktime
            ]);
        }

        $result = $pickup->simplePaginate($perPage);

        return $result;
    }
}
