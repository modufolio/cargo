<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Sender;
use App\Models\Debtor;
use App\Models\Receiver;
use App\Models\User;
use Indonesia;
use Carbon\Carbon;

class AddressRepository
{
    protected $address;
    protected $sender;
    protected $user;
    protected $indo;
    protected $debtor;
    protected $receiver;

    public function __construct(
        Address $address,
        User $user,
        Indonesia $indo,
        Sender $sender,
        Debtor $debtor,
        Receiver $receiver
    )
    {
        $this->address = $address;
        $this->user = $user;
        $this->indo = $indo;
        $this->sender = $sender;
        $this->debtor = $debtor;
        $this->receiver = $receiver;
    }

    /**
     * Get all roles.
     *
     * @return Address $address
     */
    public function getAll()
    {
        return $this->address->get();
    }

    /**
     * Get address by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->address->where('id', $id)->get();
    }

    /**
     * Update Address
     *
     * @param $data
     * @return Address
     */
    public function delete($id)
    {
        $address = $this->address->findOrFail($id);
        $address->delete();
        return $address;
    }

    /**
     * Get address by user id
     *
     * @param $id
     * @return mixed
     */
    public function getByUserId($id)
    {
        return $this->user->find($id)->addresses()->where('temporary', false)->get();
    }

    /**
     * Save Address
     *
     * @param $data
     * @return Address
     */
    public function save($data)
    {

        if ($data['is_primary']) {
            $addressUser = $this->address->where('user_id', $data['userId'])->update(['is_primary' => false]);
        }

        $user = $this->user->find($data['userId']);

        $address = $user->addresses()->create([
            'is_primary'    => $data['is_primary'],
            'temporary'     => $data['temporary'],
            'title'         => $data['title'],
            'receiptor'     => $data['receiptor'],
            'phone'         => $data['phone'],
            'province'      => $data['province'],
            'city'          => $data['city'],
            'district'      => $data['district'],
            'village'       => $data['village'],
            'postal_code'   => $data['postal_code'],
            'street'        => $data['street'],
            'notes'         => $data['notes'],
            'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            'updated_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
        ]);

        return $address->fresh();
    }

    /**
     * Update Address
     *
     * @param $data
     * @return Address
     */
    public function update($data, $id)
    {
        if ($data['is_primary']) {
            $this->updatePrimaryAddress($data['userId'], $id, false);
        }

        $address = $this->address->find($id);

        $address->is_primary = $data['is_primary'];
        $address->title = $data['title'];
        $address->receiptor = $data['receiptor'];
        $address->phone = $data['phone'];
        $address->province = $data['province'];
        $address->city = $data['city'];
        $address->district = $data['district'];
        $address->postal_code = $data['postal_code'];
        $address->street = $data['street'];
        $address->notes = $data['notes'];

        $address->update();

        return $address;
    }

    public function updatePrimaryAddress($userId, $addressId, $isPrimary)
    {
        $addressUser = $this->address->where('user_id', $userId)->where('id', '!==', $addressId)->update(['is_primary' => $isPrimary]);
        return $addressUser->fresh();
    }

    public function validateAddress($data, $userId)
    {
        $sender = $this->sender->findOrFail($data['senderId']);
        if ($sender->user_id == $userId) {
            $sender = true;
        } else {
            $sender = false;
        }

        $receiver = $this->receiver->findOrFail($data['receiverId']);
        if ($receiver->user_id == $userId) {
            $receiver = true;
        } else {
            $receiver = false;
        }

        $debtor = $this->debtor->findOrFail($data['debtorId']);
        if ($debtor->user_id == $userId) {
            $debtor = true;
        } else {
            $debtor = false;
        }

        $data = (object)[
            'sender' => $sender,
            'receiver' => $receiver,
            'debtor' => $debtor
        ];
        return $data;

    }
}
