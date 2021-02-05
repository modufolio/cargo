<?php

namespace App\Repositories;

use App\Models\Receiver;
use App\Models\User;
use Carbon\Carbon;

class ReceiverRepository
{
    protected $receiver;
    protected $user;

    public function __construct(Receiver $receiver, User $user)
    {
        $this->receiver = $receiver;
        $this->user = $user;
    }

    /**
     * Get all roles.
     *
     * @return Receiver $receiver
     */
    public function getAll()
    {
        return $this->receiver->get();
    }

    /**
     * Get sender by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        $receiver = $this->receiver->findOrFail($id);
        return $receiver;
    }

    /**
     * Get sender by user id
     *
     * @param $id
     * @return mixed
     */
    public function getByUserId($id)
    {
        return $this->user->find($id)->receivers()->where('temporary', false)->get();
    }

    /**
     * Delete data Receiver
     *
     * @param $id
     * @param $userId
     * @return Receiver
     */
    public function delete($id, $userId)
    {
        $receiver = $this->receiver->findOrFail($id);
        if ($receiver['user_id'] !== $userId) {
            return false;
        }
        $receiver->delete();
        return $receiver;
    }

    /**
     * Save Receiver Address
     *
     * @param $data
     * @return Receiver
     */
    public function save($data)
    {

        $user = $this->user->find($data['userId']);

        $receiver = $user->receivers()->create([
            'temporary'     => $data['temporary'],
            'title'         => $data['title'],
            'name'          => $data['name'],
            'phone'         => $data['phone'],
            'province'      => $data['province'],
            'city'          => $data['city'],
            'district'      => $data['district'],
            'village'       => $data['village'],
            'postal_code'   => $data['postal_code'],
            'street'        => $data['street'],
            'notes'         => $data['notes'] ?? null,
            'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            'updated_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
        ]);

        return $receiver->fresh();
    }

    /**
     * Update Receiver
     *
     * @param $data
     * @return Receiver
     */
    public function update($data, $id)
    {
        $receiver = $this->receiver->findOrFail($id);

        if ($receiver['user_id'] !== $data['userId']) {
            return false;
        }

        $receiver->title = $data['title'];
        $receiver->name = $data['name'];
        $receiver->phone = $data['phone'];
        $receiver->province = $data['province'];
        $receiver->city = $data['city'];
        $receiver->district = $data['district'];
        $receiver->village = $data['village'];
        $receiver->postal_code = $data['postal_code'];
        $receiver->street = $data['street'];
        $receiver->notes = $data['notes'];
        $receiver->temporary = $data['temporary'];

        $receiver->save();

        return $receiver;
    }
}
