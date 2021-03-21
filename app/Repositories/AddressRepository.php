<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Sender;
use App\Models\Debtor;
use App\Models\Receiver;
use App\Models\User;
use Indonesia;
use Carbon\Carbon;
use InvalidArgumentException;

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

        $user = $this->user->find($data['userId']);

        $address = $user->address()->create([
            'province'      => $data['province'],
            'city'          => $data['city'],
            'district'      => $data['district'],
            'village'       => $data['village'],
            'postal_code'   => $data['postal_code'],
            'street'        => $data['street'],
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
        $user = $this->user->find($id);
        $address = $user->address;
        if (!$address) {
            $address = $user->address()->create([
                'province'      => $data['province'],
                'city'          => $data['city'],
                'district'      => $data['district'],
                'village'       => $data['village'],
                'postal_code'   => $data['postal_code'],
                'street'        => $data['street'],
                'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);
        }

        $address->province = $data['province'];
        $address->city = $data['city'];
        $address->district = $data['district'];
        $address->village = $data['village'];
        $address->street = $data['street'];
        $address->postal_code = $data['postal_code'];

        $address->save();

        return $address;
    }

    public function updatePrimaryAddress($userId, $addressId, $isPrimary)
    {
        $addressUser = $this->address->where('user_id', $userId)->where('id', '!==', $addressId)->update(['is_primary' => $isPrimary]);
        return $addressUser->fresh();
    }

    public function validateAddress($data, $userId)
    {
        $sender = $this->sender->find($data['senderId']);
        if (!$sender || $sender->user_id !== $userId) {
            throw new InvalidArgumentException('Alamat pengirim tidak ditemukan');
        }

        $receiver = $this->receiver->find($data['receiverId']);
        if (!$receiver || $receiver->user_id !== $userId) {
            throw new InvalidArgumentException('Alamat penerima tidak ditemukan');
        }

        $debtor = $this->debtor->find($data['debtorId']);
        if (!$debtor || $debtor->user_id !== $userId) {
            throw new InvalidArgumentException('Alamat penagihan tidak ditemukan');
        }

        $data = (object)[
            'sender' => $sender,
            'receiver' => $receiver,
            'debtor' => $debtor
        ];

        return $data;
    }
}
