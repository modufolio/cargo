<?php

namespace App\Repositories;

use App\Models\Billing;
use App\Models\User;
use Indonesia;

class BillingRepository
{
    protected $billing;
    protected $user;
    protected $indo;

    public function __construct(Billing $billing, User $user, Indonesia $indo)
    {
        $this->billing = $billing;
        $this->user = $user;
        $this->indo = $indo;
    }

    /**
     * Get all billing.
     *
     * @return Billing $billing
     */
    public function getAll()
    {
        return $this->billing->get();
    }

    /**
     * Get Billing by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->billing->where('id', $id)->get();
    }

    /**
     * Get Billing by user id
     *
     * @param $id
     * @return mixed
     */
    public function getByUserId($id)
    {
        return $this->user->find($id)->Billing()->get();
        // return $this->billing->where('user_id', $id)->get();
    }

    /**
     * Update Billing
     *
     * @param $data
     * @return Billing
     */
    public function delete($id)
    {
        $billing = $this->billing->find($id);
        $billing->delete();
        return $billing;
    }

    /**
     * Save Billing
     *
     * @param $data
     * @return Billing
     */
    public function save($data)
    {

        if ($data['is_primary']) {
            $billingUser = $this->billing->where('user_id', $data['userId'])->update(['is_primary' => false]);
        }

        $user = $this->user->find($data['userId']);

        $billing = $user->Billing()->create([
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

        return $billing->fresh();
    }

    /**
     * Update Billing
     *
     * @param $data
     * @return Billing
     */
    public function update($data, $id)
    {
        if ($data['is_primary']) {
            $this->updatePrimaryBilling($data['userId'], $id, false);
        }

        $billing = $this->billing->find($id);

        $billing->is_primary = $data['is_primary'];
        $billing->title = $data['title'];
        $billing->receiptor = $data['receiptor'];
        $billing->phone = $data['phone'];
        $billing->province = $data['province'];
        $billing->city = $data['city'];
        $billing->district = $data['district'];
        $billing->postal_code = $data['postal_code'];
        $billing->street = $data['street'];
        $billing->notes = $data['notes'];

        $billing->update();

        return $billing;
    }

    public function updatePrimaryBilling($userId, $billingId, $isPrimary)
    {
        $billingUser = $this->billing->where('user_id', $userId)->where('id', '!==', $billingId)->update(['is_primary' => $isPrimary]);
        return $billingUser->fresh();
    }

    public function getAllProvince()
    {
        $data = $this->indo::allProvinces();
        return $data;
    }
}
