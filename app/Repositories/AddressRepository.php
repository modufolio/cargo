<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\User;
use Indonesia;

class AddressRepository
{
    protected $address;
    protected $user;
    protected $indo;

    public function __construct(Address $address, User $user, Indonesia $indo)
    {
        $this->address = $address;
        $this->user = $user;
        $this->indo = $indo;
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
        $address = $this->address->find($id);
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
        return $this->user->find($id)->address()->get();
        // return $this->address->where('user_id', $id)->get();
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

        $address = $user->address()->create([
            'is_primary' => $data['is_primary'],
            'title' => $data['title'],
            'receiptor' => $data['receiptor'],
            'phone' => $data['phone'],
            'province' => $data['province'],
            'city' => $data['city'],
            'district' => $data['district'],
            'postal_code' => $data['postal_code'],
            'street' => $data['street'],
            'notes' => $data['notes'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
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

    public function getAllProvince()
    {
        $data = $this->indo::allProvinces();
        return $data;
    }
}
