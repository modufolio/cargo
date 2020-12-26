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
        $pickup->address_sender     = $data['addressSender'];
        $pickup->address_receiver   = $data['addressReceiver'];
        $pickup->address_billing    = $data['addressBilling'];
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
}
