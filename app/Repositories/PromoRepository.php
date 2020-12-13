<?php

namespace App\Repositories;

use App\Models\Promo;
use App\Models\User;

class PromoRepository
{
    protected $promo;
    protected $user;

    public function __construct(Promo $promo, User $user)
    {
        $this->promo = $promo;
        $this->user = $user;
    }

    /**
     * Get all promos.
     *
     * @return Promo $promo
     */
    public function getAll()
    {
        return $this->promo->get();
    }

    /**
     * Get Promo by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->promo->where('id', $id)->get();
    }

    /**
     * Save Promo
     *
     * @param $data
     * @return Promo
     */
    public function save($data)
    {
        $promo = $user->promo()->create([
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

        return $promo->fresh();
    }

    /**
     * Update Promo
     *
     * @param $data
     * @return Promo
     */
    public function update($data, $id)
    {
        if ($data['is_primary']) {
            $this->updatePrimaryPromo($data['userId'], $id, false);
        }

        $promo = $this->promo->find($id);

        $promo->is_primary = $data['is_primary'];
        $promo->title = $data['title'];
        $promo->receiptor = $data['receiptor'];
        $promo->phone = $data['phone'];
        $promo->province = $data['province'];
        $promo->city = $data['city'];
        $promo->district = $data['district'];
        $promo->postal_code = $data['postal_code'];
        $promo->street = $data['street'];
        $promo->notes = $data['notes'];

        $promo->update();

        return $promo;
    }

    /**
     * Update Promo
     *
     * @param $data
     * @return Promo
     */
    public function delete($id)
    {
        $promo = $this->promo->find($id);
        $promo->delete();
        return $promo;
    }

    public function updatePrimaryPromo($userId, $promoId, $isPrimary)
    {
        $promoUser = $this->promo->where('user_id', $userId)->where('id', '!==', $promoId)->update(['is_primary' => $isPrimary]);
        return $promoUser->fresh();
    }
}
