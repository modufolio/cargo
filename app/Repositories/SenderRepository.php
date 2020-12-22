<?php

namespace App\Repositories;

use App\Models\Sender;
use App\Models\User;
use Carbon\Carbon;

class SenderRepository
{
    protected $sender;
    protected $user;

    public function __construct(Sender $sender, User $user)
    {
        $this->sender = $sender;
        $this->user = $user;
    }

    /**
     * Get all roles.
     *
     * @return Sender $sender
     */
    public function getAll()
    {
        return $this->sender->get();
    }

    /**
     * Get sender by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->sender->where('id', $id)->get();
    }

    /**
     * Get sender by user id
     *
     * @param $id
     * @return mixed
     */
    public function getByUserId($id)
    {
        return $this->user->find($id)->senders()->where('temporary', false)->get();
    }

    /**
     * Update Sender
     *
     * @param $data
     * @return Sender
     */
    public function delete($id)
    {
        $sender = $this->sender->findOrFail($id);
        $sender->delete();
        return $sender;
    }

    /**
     * Save Sender
     *
     * @param $data
     * @return Sender
     */
    public function save($data)
    {

        if ($data['is_primary']) {
            $sender = $this->sender->where('user_id', $data['userId'])->update(['is_primary' => false]);
        }

        $user = $this->user->find($data['userId']);

        $sender = $user->senders()->create([
            'is_primary'    => $data['is_primary'],
            'temporary'     => $data['temporary'],
            'title'         => $data['title'],
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

        return $sender->fresh();
    }

    /**
     * Update Sender
     *
     * @param $data
     * @return Sender
     */
    public function update($data, $id)
    {
        if ($data['is_primary']) {
            $this->updatePrimaryAddress($data['userId'], $id, false);
        }

        $sender = $this->sender->find($id);

        $sender->is_primary = $data['is_primary'];
        $sender->title = $data['title'];
        $sender->receiptor = $data['receiptor'];
        $sender->phone = $data['phone'];
        $sender->province = $data['province'];
        $sender->city = $data['city'];
        $sender->district = $data['district'];
        $sender->postal_code = $data['postal_code'];
        $sender->street = $data['street'];
        $sender->notes = $data['notes'];

        $sender->update();

        return $sender;
    }

    public function updatePrimaryAddress($userId, $sender, $isPrimary)
    {
        $sender = $this->sender->where('user_id', $userId)->where('id', '!==', $sender)->update(['is_primary' => $isPrimary]);
        return $sender->fresh();
    }
}
