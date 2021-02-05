<?php

namespace App\Repositories;

use App\Models\Debtor;
use App\Models\User;
use Carbon\Carbon;

class DebtorRepository
{
    protected $debtor;
    protected $user;

    public function __construct(Debtor $debtor, User $user)
    {
        $this->debtor = $debtor;
        $this->user = $user;
    }

    /**
     * Get all debtors.
     *
     * @return Debtor $debtor
     */
    public function getAll()
    {
        return $this->debtor->get();
    }

    /**
     * Get debtor by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        $debtor = $this->debtor->findOrFail($id);
        return $debtor;
    }

    /**
     * Get debtor by user id
     *
     * @param $id
     * @return mixed
     */
    public function getByUserId($id)
    {
        return $this->user->find($id)->debtors()->where('temporary', false)->get();
    }

    /**
     * Delete Debtor data
     *
     * @param $id
     * @return Debtor
     */
    public function delete($id, $userId)
    {
        $debtor = $this->debtor->findOrFail($id);
        if ($debtor['user_id'] !== $userId) {
            return false;
        }
        $debtor->delete();
        return $debtor;
    }

    /**
     * Save Debtor Address
     *
     * @param $data
     * @return Debtor
     */
    public function save($data)
    {
        $user = $this->user->find($data['userId']);

        $debtor = $user->debtors()->create([
            'title'         => $data['title'] ?? null,
            'name'          => $data['name'],
            'phone'         => $data['phone'],
            'province'      => $data['province'],
            'city'          => $data['city'],
            'district'      => $data['district'],
            'village'       => $data['village'],
            'postal_code'   => $data['postal_code'],
            'street'        => $data['street'],
            'temporary'     => $data['temporary'],
            'notes'         => $data['notes'] ?? null,
            'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            'updated_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
        ]);

        return $debtor->fresh();
    }

    /**
     * Update Debtor
     *
     * @param int $id
     * @param array $data
     * @return Debtor $debtor
     */
    public function update($data, $id)
    {
        $debtor = $this->debtor->findOrFail($id);

        if ($debtor['user_id'] !== $data['userId']) {
            return false;
        }

        $debtor->title = $data['title'];
        $debtor->name = $data['name'];
        $debtor->phone = $data['phone'];
        $debtor->province = $data['province'];
        $debtor->city = $data['city'];
        $debtor->district = $data['district'];
        $debtor->village = $data['village'];
        $debtor->postal_code = $data['postal_code'];
        $debtor->street = $data['street'];
        $debtor->notes = $data['notes'];
        $debtor->save();
        return $debtor;
    }
}
