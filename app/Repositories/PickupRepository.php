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
     * @param Address $address
     * @param user_id $userId
     * @param fleet_id $fleetId
     * @return Pickup
     */
    public function save($address, $userId, $fleetId)
    {
        $pickup = new $this->pickup;

        $pickup->fleet_id           = $fleetId;
        $pickup->user_id            = $userId;
        $pickup->promo_id           = $address['promoId'] ?? null;
        $pickup->name               = $address['name'];
        $pickup->phone              = $address['phone'];
        $pickup->address_sender     = $address['addressSender'];
        $pickup->address_recipient  = $address['addressRecepient'];
        $pickup->address_billing    = $address['addressBilling'];
        $pickup->notes              = $address['notes'];
        $pickup->picktime           = $address['picktime'];
        $pickup->save();

        return $pickup->fresh();
    }
}
