<?php

namespace App\Repositories;

use App\Models\Promo;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Pickup;

class PromoRepository
{
    protected $promo;
    protected $user;
    protected $pickup;

    public function __construct(Promo $promo, User $user, Pickup $pickup)
    {
        $this->promo = $promo;
        $this->user = $user;
        $this->pickup = $pickup;
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
     * Get all promo can be used.
     *
     * @return Promo $promo
     */
    public function listPromoCanBeUsed($userId)
    {
        $this->promo->get();
        $this->user->find($id)->pickups()->get();
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
     * Get Promo by code
     *
     * @param $code
     * @return mixed
     */
    public function getByCode($code = '')
    {
        $promo = $this->promo->where('code', $code)->first();
        return $promo;
    }

    /**
     * Save Promo
     *
     * @param $data
     * @return Promo
     */
    public function save($data)
    {
        $promo = new $this->promo;
        $promo->discount = $data['discount'];
        $promo->discount_max = $data['discount_max'];
        $promo->min_value = $data['min_value'];
        $promo->start_at = $data['start_at'];
        $promo->end_at = $data['end_at'];
        $promo->max_used = $data['max_used'];
        $promo->description = $data['description'];
        $promo->code = $data['code'];
        $promo->term = $data['term'];
        $promo->save();
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
        $promo = $this->promo->find($id);
        $promo->discount = $data['discount'];
        $promo->discount_max = $data['discount_max'];
        $promo->min_value = $data['min_value'];
        $promo->start_at = $data['start_at'];
        $promo->end_at = $data['end_at'];
        $promo->max_used = $data['max_used'];
        $promo->description = $data['description'];
        $promo->code = $data['code'];
        $promo->term = $data['term'];
        $promo->update();
        return $promo->fresh();
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

    public function canUsePromo($promoCode, $userId)
    {
        $promo = $this->getByCode($promoCode);
        $pickup = $this->pickup->where([
            ['promo_id', '=', $promo['id']],
            ['user_id'], '=', $userId
        ])->get();

    }

}
