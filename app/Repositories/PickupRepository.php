<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Pickup;
use App\Models\PickupPlan;
use App\Models\Item;
use App\Models\User;
use Indonesia;
use Carbon\Carbon;
use InvalidArgumentException;

class PickupRepository
{
    protected $pickup;
    protected $pickupPlan;
    protected $item;

    public function __construct(Pickup $pickup, PickupPlan $pickupPlan, Item $item)
    {
        $this->pickup = $pickup;
        $this->pickupPlan = $pickupPlan;
        $this->item = $item;
    }

    /**
     * Save Pickup Address / alamat pickup / alamat pengirim
     *
     * @param array $data
     * @param Promo $promo
     * @return Pickup
     */
    public function createPickupRepo($data, $promo)
    {
        $pickup = new $this->pickup;

        $pickup->fleet_id           = $data['fleetId'];
        $pickup->user_id            = $data['userId'];
        $pickup->promo_id           = $promo['id'] ?? null;
        $pickup->name               = $data['name'];
        $pickup->phone              = $data['phone'];
        $pickup->sender_id          = $data['senderId'];
        $pickup->receiver_id        = $data['receiverId'];
        $pickup->debtor_id          = $data['debtorId'];
        $pickup->notes              = $data['notes'];
        $pickup->picktime           = $data['picktime'];
        $pickup->created_by         = $data['userId'];
        $pickup->status             = 'request';
        $pickup->save();

        return $pickup;
    }

    /**
     * create pickup plan
     */
    public function savePickupPlanRepo($data)
    {
        $pickup = new $this->pickup;

        $pickup->fleet_id           = $data['fleetId'];
        $pickup->user_id            = $data['userId'];
        $pickup->promo_id           = $promo['id'] ?? null;
        $pickup->name               = $data['name'];
        $pickup->phone              = $data['phone'];
        $pickup->sender_id          = $data['senderId'];
        $pickup->receiver_id        = $data['receiverId'];
        $pickup->debtor_id          = $data['debtorId'];
        $pickup->notes              = $data['notes'];
        $pickup->picktime           = $data['picktime'];
        $pickup->save();

        return $pickup;
    }

    /**
     * Save get pickup by userId
     *
     * @param Pickup $pickup
     */
    public function getByUserId($pickup)
    {
        return $this->user->find($id)->pickups()->get();
    }

    /**
     * get all pickup pagination
     *
     * @param Pickup $pickup
     */
    public function getAllPickupPaginate($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $sort = $data['sort'];

        $pickup = $this->pickup->with(['user','sender','receiver','debtor','fleet','promo']);

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickup = $pickup->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickup = $pickup->sortable([
                        'picktime' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($picktime)) {
            $pickup = $pickup->where('picktime', 'ilike', '%'.$picktime.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get ready to pickup pagination
     *
     * @param array $data
     */
    public function getReadyToPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $sort = $data['sort'];

        $pickup = $this->pickup->whereNull('pickup_plan_id')->with(['sender' => function($q) {
            $q->select('id','city','district','village');
        },'items' => function($q) {
            $q->select('id','weight','volume','pickup_id');
        }])->select('name','id','sender_id','picktime');

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickup = $pickup->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickup = $pickup->sortable([
                        'picktime' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($picktime)) {
            $pickup = $pickup->where('picktime', 'ilike', '%'.$picktime.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get list pickup plan
     *
     * @param array $data
     */
    public function getListPickupPlanRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $status = $data['status'];
        $driver = $data['driver'];
        $licenseNumber = $data['licenseNumber'];
        $vehicleType = $data['vehicleType'];
        $sort = $data['sort'];

        $pickupPlan = $this->pickupPlan->with(['vehicle.driver.user', 'pickups']);

        if (empty($perPage)) {
            $perPage = 10;
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
            // switch ($sort['field']) {
            //     case 'id':
            //         $pickupPlan = $pickupPlan->sortable([
            //             'id' => $order
            //         ]);
            //         break;
            //     case 'user.name':
            //         $pickupPlan = $pickupPlan->sortable([
            //             'user.name' => $order
            //         ]);
            //         break;
            //     case 'sender.city':
            //         $pickupPlan = $pickupPlan->sortable([
            //             'sender.city' => $order
            //         ]);
            //         break;
            //     case 'sender.district':
            //         $pickupPlan = $pickupPlan->sortable([
            //             'sender.district' => $order
            //         ]);
            //         break;
            //     case 'sender.village':
            //         $pickupPlan = $pickupPlan->sortable([
            //             'sender.village' => $order
            //         ]);
            //         break;
            //     case 'picktime':
            //         $pickupPlan = $pickupPlan->sortable([
            //             'picktime' => $order
            //         ]);
            //         break;
            //     default:
            //         $pickupPlan = $pickupPlan->sortable([
            //             'id' => 'desc'
            //         ]);
            //         break;
            // }
        }

        if (!empty($id)) {
            $pickupPlan = $pickupPlan->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($startDate) && !empty($endDate)) {
            $pickupPlan = $pickupPlan->whereHas('pickups', function ($q) use ($startDate, $endDate){
                $q->whereDate('picktime', '>=', date($startDate))
                    ->whereDate('picktime', '<=', date($endDate));
            });
        }

        if (!empty($status)) {
            $pickupPlan = $pickupPlan->where('status', 'ilike', '%'.$status.'%');
        }

        if (!empty($driver)) {
            $pickupPlan = $pickupPlan->whereHas('vehicle', function($v) use ($driver) {
                $v->whereHas('driver', function($d) use ($driver) {
                    $d->whereHas('user', function($u) use ($driver) {
                        $u->where('name', 'ilike', '%'.$driver.'%');
                    });
                });
            });
        }

        if (!empty($licenseNumber)) {
            $pickupPlan = $pickupPlan->whereHas('vehicle', function($q) use ($licenseNumber) {
                $q->where('license_plate', 'ilike', '%'.$licenseNumber.'%');
            });
        }

        if (!empty($vehicleType)) {
            $pickupPlan = $pickupPlan->whereHas('vehicle', function($q) use ($vehicleType) {
                $q->where('type', 'ilike', '%'.$vehicleType.'%');
            });
        }

        $result = $pickupPlan->paginate($perPage);

        return $result;
    }

    /**
     * get list pickup inside pickup plan
     *
     * @param array $data
     */
    public function getPickupByPickupPlanRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $sort = $data['sort'];

        $pickup = $this->pickup->with(['user','sender','pickupPlan' => function($q) {
            $q->select('id','created_at');
        }])->where('pickup_plan_id', $data['pickupPlanId']);

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'name':
                    $pickup = $pickup->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * check pickups request date
     *
     * @param array $data
     */
    public function checkPickupRequestDate($data = [])
    {
        $pickup = Pickup::select('picktime')->whereIn('id', $data)->get()->pluck('picktime');
        $pickup = collect($pickup)->toArray();
        $result = [];
        foreach ($pickup as $key => $value) {
            $result[] = Carbon::parse($value)->format('Y-m-d');
        }
        if (count(array_unique($result)) === 1) {
            return $result;
        }
        throw new InvalidArgumentException('Maaf, ada permintaan tanggal pickup yang berbeda');
    }

    /**
     * get pickup pagination by customer id
     *
     * @param Pickup $pickup
     */
    public function getPickupByCustomerRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $sort = $data['sort'];

        $pickup = $this->pickup->where('user_id', $data['userId'])->with(['user','sender','receiver','debtor','fleet','promo']);

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickup = $pickup->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickup = $pickup->sortable([
                        'picktime' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($picktime)) {
            $pickup = $pickup->where('picktime', 'ilike', '%'.$picktime.'%');
        }

        $result = $pickup->simplePaginate($perPage);

        return $result;
    }

    /**
     * get list pickup plan driver
     * @param array $data
     */
    public function getListPickupPlanDriverRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $userId = $data['userId'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $status = $data['status'];
        $licenseNumber = $data['licenseNumber'];
        $vehicleType = $data['vehicleType'];
        $sort = $data['sort'];

        $pickupPlan = $this->pickupPlan->where('status', 'applied')->whereHas('vehicle', function($q) use ($userId) {
            $q->whereHas('driver', function($o) use ($userId) {
                $o->whereHas('user', function($p) use ($userId) {
                    $p->where('id', $userId);
                });
            });
        })->with(['pickups:id,status,pickup_plan_id']);

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickupPlan = $pickupPlan->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickupPlan = $pickupPlan->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickupPlan = $pickupPlan->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickupPlan = $pickupPlan->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickupPlan = $pickupPlan->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickupPlan = $pickupPlan->sortable([
                        'picktime' => $order
                    ]);
                    break;
                default:
                    $pickupPlan = $pickupPlan->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickupPlan = $pickupPlan->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($startDate) && !empty($endDate)) {
            $pickupPlan = $pickupPlan->whereHas('pickups', function ($q) use ($startDate, $endDate){
                $q->whereDate('picktime', '>=', date($startDate))
                    ->whereDate('picktime', '<=', date($endDate));
            });
        }

        if (!empty($status)) {
            $pickupPlan = $pickupPlan->where('status', 'ilike', '%'.$status.'%');
        }

        if (!empty($licenseNumber)) {
            $pickupPlan = $pickupPlan->whereHas('vehicle', function($q) use ($licenseNumber) {
                $q->where('license_plate', 'ilike', '%'.$licenseNumber.'%');
            });
        }

        if (!empty($vehicleType)) {
            $pickupPlan = $pickupPlan->whereHas('vehicle', function($q) use ($vehicleType) {
                $q->where('type', 'ilike', '%'.$vehicleType.'%');
            });
        }

        $result = $pickupPlan->simplePaginate($perPage);

        return $result;
    }

    /**
     * get ready to pickup order inside pickup plan pagination
     * driver only
     *
     * @param array $data
     */
    public function getReadyToPickupDriverRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $userId = $data['userId'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $sort = $data['sort'];

        $pickup = $this->pickup->whereNull('pickup_plan_id')->with(['user','sender','receiver','debtor','fleet','promo']);

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'user.name':
                    $pickup = $pickup->sortable([
                        'user.name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickup = $pickup->sortable([
                        'picktime' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($picktime)) {
            $pickup = $pickup->where('picktime', 'ilike', '%'.$picktime.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get list pickup inside pickup plan
     * driver only
     *
     * @param array $data
     */
    public function getPickupByPickupPlanDriverRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $userId = $data['userId'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $street = $data['street'];
        $sort = $data['sort'];

        $pickup = $this->pickup->select('id', 'name', 'phone','sender_id')->with([
            'sender' => function ($q) {
                $q->select('id','street');
            },
            'proofOfPickup' => function ($q) {
                $q->select('id','pickup_id','status','driver_pick','status_pick');
            }
        ])->where('pickup_plan_id', $data['pickupPlanId']);

        if (empty($perPage)) {
            $perPage = 10;
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
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'name':
                    $pickup = $pickup->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'sender.city':
                    $pickup = $pickup->sortable([
                        'sender.city' => $order
                    ]);
                    break;
                case 'sender.district':
                    $pickup = $pickup->sortable([
                        'sender.district' => $order
                    ]);
                    break;
                case 'sender.village':
                    $pickup = $pickup->sortable([
                        'sender.village' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($city)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($city) {
                $q->where('city', 'ilike', '%'.$city.'%');
            });
        }

        if (!empty($district)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($district) {
                $q->where('district', 'ilike', '%'.$district.'%');
            });
        }

        if (!empty($village)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($village) {
                $q->where('village', 'ilike', '%'.$village.'%');
            });
        }

        if (!empty($street)) {
            $pickup = $pickup->whereHas('sender', function($q) use ($street) {
                $q->where('street', 'ilike', '%'.$street.'%');
            });
        }

        $result = $pickup->simplePaginate($perPage);

        return $result;
    }

    /**
     * get total volume and kilo of pickup inside pickup plan
     *
     * @param array $data
     */
    public function getTotalVolumeAndKiloPickupRepo($data = [])
    {
        $pickups = $this->pickup->select('id')->where('pickup_plan_id', $data['pickupPlanId'])->get();
        $sumVol = 0;
        $sumKilo = 0;
        foreach ($pickups as $key => $value) {
            $items = $this->item->where('pickup_id', $value['id'])->get();
            foreach ($items as $k => $v) {
                if ($v['unit_id'] == 3) {
                    if(isset($v['unit_total'])) {
                        $sumVol += $v['unit_total'];
                    }
                } else {
                    if(isset($v['unit_total'])) {
                        $sumKilo += $v['unit_total'];
                    }
                }
            }
        }
        $data = [
            'volume' => $sumVol,
            'kilo' => $sumKilo
        ];
        return $data;
    }

    /**
     * get detail pickup order for driver
     * @param array $data
     */
    public function getDetailPickupRepo($data = [])
    {
        $pickup = $this->pickup->select('id','name','phone','picktime','sender_id','receiver_id')->where('id', $data['pickupId'])->with(
            [
                'sender' => function($q) {
                    $q->select('id', 'province','city','district','village','postal_code','street');
                },
                'receiver' => function($q) {
                    $q->select('id','province','city','district');
                },
                'items' => function($q) {
                    $q->select('id','name','pickup_id','unit_total','unit_count','service_id','unit_id');
                },
                'items.service' => function($q) {
                    $q->select('id','name');
                },
                'items.unit' => function($q) {
                    $q->select('id','name');
                }
            ])->first();

        if (!$pickup) {
            throw new InvalidArgumentException('Maaf, ada pickup order tidak ditemukan');
        }

        return $pickup;

    }

    /**
     * check pickup have pickup plan
     * @param array $data
     */
    public function checkPickupHasPickupPlan($data = [])
    {
        $pickup = $this->pickup->find($data['pickupId']);
        if (!$pickup) {
            throw new InvalidArgumentException('Pickup tidak ditemukan');
        }
        if ($pickup['pickup_plan_id'] == null) {
            throw new InvalidArgumentException('Pickup ini tidak memiliki pickup plan');
        }
    }

    /**
     * get detail pickup order for web
     * @param array $data
     */
    public function getDetailPickupAdminRepo($data = [])
    {
        $pickup = $this->pickup->select('id','name','phone','picktime','sender_id','receiver_id','pickup_plan_id','status')->where('id', $data['pickupId'])->with(
            [
                'sender' => function($q) {
                    $q->select('id', 'province','city','district','village','postal_code','street');
                },
                'items' => function($q) {
                    $q->select('id','name','pickup_id','unit_count','service_id','weight','volume');
                },
                // 'items.unit' => function($q) {
                //     $q->select('id','name');
                // },
                'items.service' => function($q) {
                    $q->select('id','name');
                },
                'pickupPlan' => function($q) {
                    $q->select('id','vehicle_id');
                },
                'pickupPlan.vehicle' => function($q) {
                    $q->select('id','driver_id');
                },
                'pickupPlan.vehicle.driver' => function($q) {
                    $q->select('id','user_id');
                },
                'pickupPlan.vehicle.driver.user' => function($q) {
                    $q->select('id','name');
                },
                'proofOfPickup' => function($q) {
                    $q->select('id', 'pickup_id', 'notes', 'status_pick');
                }
            ])->first();

        if (!$pickup) {
            throw new InvalidArgumentException('Maaf, ada pickup order tidak ditemukan');
        }
        return $pickup;
    }

    /**
     * create pickup plan
     * @param array $data
     */
    public function updatePickupRepo($data = [])
    {
        $pickup = $this->pickup->find($data['pickup']['id']);
        if (!$pickup) {
            throw new InvalidArgumentException('Gagal merubah status pickup');
        }
        $pickup->status           = $data['pickup']['status'];
        $pickup->save();

        return $pickup;
    }
}
