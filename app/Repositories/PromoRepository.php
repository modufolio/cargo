<?php

namespace App\Repositories;

use App\Models\Promo;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

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
     * @param int $id
     * @return false
     * @return Promo
     */
    public function getById($id = null)
    {
        if ($id == null || $id == '') {
            throw new InvalidArgumentException('Promo tidak ditemukan');
        }
        $promo = $this->promo->find($id);
        if (!$promo) {
            throw new InvalidArgumentException('Promo tidak ditemukan');
        }
        return $promo;
    }

    /**
     * Get Promo by user id
     *
     * @param Array $data
     * @return mixed
     */
    public function getUserId($data)
    {
        return $this->user->find($data['userId'])->promos;
    }

    /**
     * Get Promo by created_by
     *
     * @param Array $data
     * @return mixed
     */
    public function getCreatedBy($data)
    {
        return $this->user->find($data['userId'])->promoOwnerships;
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
        $promo->created_by = $data['userId'];
        $promo->user_id = $data['targetPromo'];
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
        return $promo;
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
        $promo->save();
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

    /**
     * validate and check user can used promo
     *
     * @param Promo $promo
     * @param array $data
     * @
     */
    public function validatePromo($promo, $data)
    {
        if ($promo['max_used'] == 0) {
            throw new InvalidArgumentException('Promo habis');
        }

        if ($promo['user_id'] !== $data['userId']) {
            throw new InvalidArgumentException('Promo tidak dapat digunakan oleh pengguna ini');
        }

        if (intval($promo['min_value']) > intval($data['value'])) {
            throw new InvalidArgumentException('Total harga tidak memenuhi syarat');
        }

        $result = true;
    }

    /**
     * Select promo
     *
     * @param array $data
     * @param Promo $promo
     * @return mixed
     */
    public function selectPromo($promo, $data)
    {
        $total = intval($data['value']);
        $minValue = intval($promo['min_value']);
        $promoDiscount = intval($promo['discount']);
        $promoDiscountMax = intval($promo['discount_max']);
        $discount = ($total * $promoDiscount) / 100;

        if (intval($discount) >= $promoDiscountMax) {
            $total = $total - $promoDiscountMax;
            $discount = intval($promoDiscountMax);
        } else {
            $total = $total - intval($discount);
        }

        $result = (object)[
            'value' => $total,
            'discount' => $discount,
            'promo_result' => $total,
        ];
        return $result;
    }


    /**
     * Get all promo paginate
     *
     * @param array $data
     * @return mixed
     */
    public function getAllPaginateRepo($data = [])
    {
        $sort = $data['sort'];
        $perPage = $data['perPage'];

        $discount = $data['discount'];
        $discountMax = $data['discountMax'];
        $minValue = $data['minValue'];
        $startAt = $data['startAt'];
        $endAt = $data['endAt'];
        $id = $data['id'];

        $promo = $this->promo->with(['user' => function($q) {
            $q->select('name','email','id');
        },'creator' => function($q) {
            $q->select('name','email','id');
        }]);

        if (empty($perPage)) {
            $perPage = 15;
        }

        if (!empty($sort['field'])) {
            $order = $sort['order'];
            if ($order == 'ascend') {
                $order = 'asc';
            } else if ($order == 'descend') {
                $order = 'desc';
            } else {
                $order = 'desc';
            }
            switch ($sort['field']) {
                case 'discount':
                    $promo = $promo->sortable([
                        'discount' => $order
                    ]);
                    break;
                case 'discount_max':
                    $promo = $promo->sortable([
                        'discount_max' => $order
                    ]);
                    break;
                case 'discount':
                    $promo = $promo->sortable([
                        'discount' => $order
                    ]);
                    break;
                case 'start_at':
                    $promo = $promo->sortable([
                        'start_at' => $order
                    ]);
                    break;
                case 'min_value':
                    $promo = $promo->sortable([
                        'min_value' => $order
                    ]);
                    break;
                case 'id':
                    $promo = $promo->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'end_at':
                    $promo = $promo->sortable([
                        'end_at' => $order
                    ]);
                    break;
                default:
                    $promo = $promo->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($discount)) {
            $promo = $promo->where('discount', 'ilike', '%'.$discount.'%');
        }

        if (!empty($id)) {
            $promo = $promo->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($discountMax)) {
            $promo = $promo->where('discount_max', 'ilike', '%'.$discountMax.'%');
        }

        if (!empty($minValue)) {
            $promo = $promo->where('min_value', 'ilike', '%'.$minValue.'%');
        }

        if (!empty($startAt)) {
            $promo = $promo->where('start_at', 'ilike', '%'.$startAt.'%');
        }

        if (!empty($endAt)) {
            $promo = $promo->where('end_at', 'ilike', '%'.$endAt.'%');
        }

        $promo = $promo->paginate($perPage);

        return $promo;
    }

}
