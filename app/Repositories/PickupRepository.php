<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Pickup;
use App\Models\PickupPlan;
use App\Models\ShipmentPlan;
use App\Models\Item;
use App\Models\User;
use Indonesia;
use Carbon\Carbon;
use DB;
use Log;
use InvalidArgumentException;
use Exception;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class PickupRepository
{
    protected $pickup;
    protected $pickupPlan;
    protected $shipmentPlan;
    protected $item;

    public function __construct(Pickup $pickup, PickupPlan $pickupPlan, Item $item, ShipmentPlan $shipmentPlan)
    {
        $this->pickup = $pickup;
        $this->pickupPlan = $pickupPlan;
        $this->item = $item;
        $this->shipmentPlan = $shipmentPlan;
    }

    /**
     * Save Pickup
     *
     * @param array $data
     * @param Promo $promo
     * @return Pickup
     */
    public function createPickupRepo($data, $promo)
    {
        $config = [
            'table' => 'pickups',
            'length' => 12,
            'field' => 'number',
            'prefix' => 'P'.Carbon::now('Asia/Jakarta')->format('ymd'),
            'reset_on_prefix_change' => true
        ];
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
        $pickup->number             = IdGenerator::generate($config);
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
        $number = $data['number'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $picktime = $data['picktime'];
        $isDrop = $data['isDrop'];
        $sort = $data['sort'];

        $pickup = $this->pickup->where('is_drop', $data['isDrop'])->with(['user','sender','receiver','debtor','fleet','promo','items','items.service','cost']);

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
                case 'number':
                    $pickup = $pickup->sortable([
                        'number' => $order
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
                        'number' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($number)) {
            $pickup = $pickup->where('number', 'ilike', '%'.$number.'%');
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

        $result = $pickup->orderBy('created_at', 'DESC')->paginate($perPage);

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
        $number = $data['number'];

        $pickup = $this->pickup->whereNull('pickup_plan_id')->with(['sender' => function($q) {
            $q->select('id','city','district','village');
        },'items' => function($q) {
            $q->select('id','weight','volume','pickup_id');
        }])->select('name','id','sender_id','picktime','number');

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
                case 'number':
                    $pickup = $pickup->sortable([
                        'number' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'number' => 'desc'
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

        if (!empty($number)) {
            $pickup = $pickup->where('number', 'ilike', '%'.$number.'%');
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
        $number = $data['number'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $status = $data['status'];
        $driver = $data['driver'];
        $licenseNumber = $data['licenseNumber'];
        $vehicleType = $data['vehicleType'];
        $sort = $data['sort'];
        $branchId = $data['branchId'];

        $pickupPlan = $this->pickupPlan->with(['vehicle.driver.user', 'pickups' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }])->whereHas('pickups', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });

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
                case 'number':
                    $pickupPlan = $pickupPlan->sortable([
                        'number' => $order
                    ]);
                    break;
                case 'status':
                    $pickupPlan = $pickupPlan->sortable([
                        'status' => $order
                    ]);
                    break;
                case 'vehicle.license_plate':
                    $pickupPlan = $pickupPlan->sortable([
                        'vehicle.license_plate' => $order
                    ]);
                    break;
                case 'vehicle.type':
                    $pickupPlan = $pickupPlan->sortable([
                        'vehicle.type' => $order
                    ]);
                    break;
                case 'created_at':
                    $pickupPlan = $pickupPlan->sortable([
                        'created_at' => $order
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
                'items',
                'items.service' => function($q) {
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
        $pickup = $this->pickup->select('id','name','phone','picktime','sender_id','receiver_id','pickup_plan_id','status','number','is_drop','fleet_id','promo_id')->where('id', $data['pickupId'])->with(
            [
                'sender' => function($q) {
                    $q->select('id', 'province','city','district','village','postal_code','street');
                },
                'receiver' => function($q) {
                    $q->select('id','city','district');
                },
                'items' => function($q) {
                    $q->select('id','name','pickup_id','unit_count','service_id','weight','volume','type','price','unit');
                },
                'cost',
                // 'items.unit' => function($q) {
                //     $q->select('id','name');
                // },
                'items.service' => function($q) {
                    $q->select('id','name');
                },
                'pickupPlan' => function($q) {
                    $q->select('id','vehicle_id','number');
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

    /**
     * update branch in pickup order
     * @param array $pickupId
     * @param int $branchid
     */
    public function updateBranchRepo($pickupId, $branchId)
    {
        DB::beginTransaction();
        try {
            $branchFrom = $this->pickup->select('branch_id', 'id')->whereIn('id', $pickupId)->get();
            $this->pickup->whereIn('id', $pickupId)->update(['branch_id' => $branchId]);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            DB::rollback();
            throw new InvalidArgumentException('Gagal mengupdate cabang pada pickup');
        }
        DB::commit();
        return $branchFrom;
    }

    /**
     * get branch in pickup order
     * @param array $pickupId
     */
    public function getPickupBranchRepo($pickupId)
    {
        DB::beginTransaction();
        try {
            $branchFrom = $this->pickup->select('branch_id', 'id')->whereIn('id', $pickupId)->get();
        } catch (Exception $e) {
            Log::info($e->getMessage());
            Log::error($e);
            DB::rollback();
            throw new InvalidArgumentException('Gagal mendapat cabang pada pickup');
        }
        DB::commit();
        return $branchFrom;
    }

    /**
     * get ready to shipment pagination
     *
     * @param array $data
     */
    public function getReadyToShipmentRepo($data = [])
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
        $number = $data['number'];
        $branchId = $data['branchId'];
        $isTransit = $data['isTransit'];

        $pickup = $this->pickup
            ->where('status', 'applied')
            ->where('branch_id', $branchId)
            ->whereNotNull('pickup_plan_id')
            ->whereNull('shipment_plan_id')
            ->where(function($q) {
                $q->doesnthave('transit')->orWhereHas('transit', function($q) {
                    $q->where('status', 'applied');
                });
            })
            ->whereHas('pickupPlan', function($q) {
                $q->where('status', 'applied');
            })
            ->with(['sender' => function($q) {
                $q->select('id','city','district','village');
            },'items' => function($q) {
                $q->select('id','weight','volume','pickup_id');
            }])->select('name','id','sender_id','picktime', 'number', 'is_transit');

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
                case 'number':
                    $pickup = $pickup->sortable([
                        'number' => $order
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
                case 'updated_at':
                    $pickup = $pickup->sortable([
                        'updated_at' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'updated_at' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($isTransit)) {
            $pickup = $pickup->where('is_transit', $isTransit);
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($number)) {
            $pickup = $pickup->where('number', 'ilike', '%'.$number.'%');
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
     * get list shipment plan
     *
     * @param array $data
     */
    public function getListShipmentPlanRepo($data = [])
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
        $branchId = $data['branchId'];

        $shipmentPlan = $this->shipmentPlan->whereHas('pickups', function($q) use ($branchId) {
            $q->whereHas('branch', function($o) use ($branchId) {
                $o->where('id', $branchId);
            });
        })->with(['vehicle.driver.user', 'pickups']);

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
                    $shipmentPlan = $shipmentPlan->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'status':
                    $shipmentPlan = $shipmentPlan->sortable([
                        'status' => $order
                    ]);
                    break;
                case 'vehicle.license_plate':
                    $shipmentPlan = $shipmentPlan->sortable([
                        'vehicle.license_plate' => $order
                    ]);
                    break;
                case 'vehicle.type':
                    $shipmentPlan = $shipmentPlan->sortable([
                        'vehicle.type' => $order
                    ]);
                    break;
                case 'created_at':
                    $shipmentPlan = $shipmentPlan->sortable([
                        'created_at' => $order
                    ]);
                    break;
                default:
                    $shipmentPlan = $shipmentPlan->sortable([
                        'updated_at' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $shipmentPlan = $shipmentPlan->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($startDate) && !empty($endDate)) {
            $shipmentPlan = $shipmentPlan->whereDate('created_at', '>=', date($startDate))->whereDate('created_at', '<=', date($endDate));
        }

        if (!empty($status)) {
            $shipmentPlan = $shipmentPlan->where('status', 'ilike', '%'.$status.'%');
        }

        if (!empty($driver)) {
            $shipmentPlan = $shipmentPlan->whereHas('vehicle', function($v) use ($driver) {
                $v->whereHas('driver', function($d) use ($driver) {
                    $d->whereHas('user', function($u) use ($driver) {
                        $u->where('name', 'ilike', '%'.$driver.'%');
                    });
                });
            });
        }

        if (!empty($licenseNumber)) {
            $shipmentPlan = $shipmentPlan->whereHas('vehicle', function($q) use ($licenseNumber) {
                $q->where('license_plate', 'ilike', '%'.$licenseNumber.'%');
            });
        }

        if (!empty($vehicleType)) {
            $shipmentPlan = $shipmentPlan->whereHas('vehicle', function($q) use ($vehicleType) {
                $q->where('type', 'ilike', '%'.$vehicleType.'%');
            });
        }

        $result = $shipmentPlan->paginate($perPage);

        return $result;
    }

    /**
     * get list pickup inside shipment plan
     *
     * @param array $data
     */
    public function getPickupByShipmentPlanRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $id = $data['id'];
        $name = $data['name'];
        $city = $data['city'];
        $district = $data['district'];
        $village = $data['village'];
        $sort = $data['sort'];
        $number = $data['number'];

        $pickup = $this->pickup->with(['user','sender','shipmentPlan' => function($q) {
            $q->select('id','created_at');
        }])->whereNotNull('pickup_plan_id')->where('shipment_plan_id', $data['shipmentPlanId']);

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
                        'updated_at' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($id)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$id.'%');
        }

        if (!empty($number)) {
            $pickup = $pickup->where('number', 'ilike', '%'.$number.'%');
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
     * update is transit branch in pickup order
     * @param array $pickupId
     */
    public function updateIsTransitBranchRepo($pickupId, $value)
    {
        return $this->pickup->whereIn('id', $pickupId)->update(['is_transit' => $value]);
    }

    /**
     * cancel shipment plan
     * @param array $data
     */
    public function cancelShipmentPlanRepo($data)
    {
        $this->pickup->where('shipment_plan_id', $data['shipmentPlanId'])-update(['shipment_plan_id' => null]);
    }

    /**
     * check pickup have shipment
     *
     */
    // public function checkPickupHaveShipment($pickupPlanId)
    // {
    //     $hasPOPApplied = $this->pickup->whereHas('proofOfPickup', function($q) {
    //         $q->where('status', 'applied');
    //     })->get();
    //     if ($hasPOPApplied) {
    //         throw new InvalidArgumentException('Maaf, Pickup ini tidak dapat dibatalkan');
    //     }
    // }            PENDING PENGERJAAN

    /**
     * Save Drop Order
     * DEPRECATED
     * @param array $data
     * @param Promo $promo
     * @return Pickup
     */
    // public function createDropAdminRepo($data, $promo)
    // {
    //     $config = [
    //         'table' => 'pickups',
    //         'length' => 12,
    //         'field' => 'number',
    //         'prefix' => 'P'.Carbon::now('Asia/Jakarta')->format('ymd'),
    //         'reset_on_prefix_change' => true
    //     ];
    //     $pickup = new $this->pickup;

    //     $pickup->fleet_id           = $data['fleetId'];
    //     $pickup->user_id            = $data['userId'];
    //     $pickup->promo_id           = $promo['id'] ?? null;
    //     $pickup->name               = $data['name'];
    //     $pickup->phone              = $data['phone'];
    //     $pickup->sender_id          = $data['senderId'];
    //     $pickup->receiver_id        = $data['receiverId'];
    //     $pickup->debtor_id          = $data['debtorId'];
    //     $pickup->notes              = $data['notes'];
    //     $pickup->picktime           = $data['picktime'];
    //     $pickup->created_by         = $data['userId'];
    //     $pickup->status             = 'applied';
    //     $pickup->number             = IdGenerator::generate($config);
    //     $pickup->is_drop            = true;
    //     $pickup->save();

    //     return $pickup;
    // }

    /**
     * Save Pickup by admin
     *
     * @param array $data
     * @param Promo $promo
     * @param object $customer
     * @param boolean $isDrop
     * @return Pickup
     */
    public function createPickupAdminRepo($data, $promo, $customer, $isDrop)
    {
        $config = [
            'table' => 'pickups',
            'length' => 12,
            'field' => 'number',
            'prefix' => 'P'.Carbon::now('Asia/Jakarta')->format('ymd'),
            'reset_on_prefix_change' => true
        ];
        $pickup = new $this->pickup;

        $pickup->fleet_id           = $data['fleetId'];
        $pickup->user_id            = $customer['id'];
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
        $pickup->number             = IdGenerator::generate($config);
        $pickup->is_drop            = $isDrop;
        $pickup->save();

        return $pickup;
    }

    /**
     * Edit Pickup by admin
     *
     * @param array $data
     * @param Promo $promo
     * @param object $customer
     * @param boolean $isDrop
     * @return Pickup
     */
    public function editPickupAdminRepo($data, $promo, $customer, $isDrop)
    {
        $pickup = $this->pickup->find($data['id']);

        $pickup->fleet_id           = $data['fleetId'];
        $pickup->user_id            = $customer['id'];
        $pickup->promo_id           = $promo['id'] ?? null;
        $pickup->name               = $data['name'];
        $pickup->phone              = $data['phone'];
        $pickup->sender_id          = $data['senderId'];
        $pickup->receiver_id        = $data['receiverId'];
        $pickup->debtor_id          = $data['debtorId'];
        $pickup->notes              = $data['notes'];
        $pickup->picktime           = $data['picktime'];
        $pickup->updated_by         = $data['userId'];
        $pickup->is_drop            = $isDrop;
        $pickup->save();

        return $pickup;
    }

    /**
     * cancel drop by admin
     */
    public function cancelDropRepo($pickupId)
    {
        $drop = $this->pickup->find($data['id']);
        if ($drop->shipment_plan_id !== null) {
            throw new InvalidArgumentException('Drop order tidak dapat dibatalkan, karena shipment plan sudah terbuat');
        }
        $drop->updated_by         = $data['userId'];
        $drop->status             = 'cancelled';
        $drop->save();
        return $drop;
    }

    /**
     * get all order in branch
     */
    public function getOrderOnBranchRepo($branchId)
    {
        $order = $this->pickup->where('branch_id', $branchId)->count();
        return $order;
    }

    /**
     * get finished pickup order paginate
     * @param array $data
     */
    public function getFinishedPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $sort = $data['sort'];

        $number = $data['number'];
        $name = $data['name'];
        $receiver = $data['receiver'];
        $debtor = $data['debtor'];
        $paymentMethod = $data['paymentMethod'];

        $branchName = $data['branchName'];

        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];

        $pickup = $this->pickup
            ->whereNotNull('pickup_plan_id')
            // ->whereHas('proofOfDelivery', function($q) {
            //     $q->where('status_delivery', 'success');
            // })
            ->with(['user','sender','receiver','debtor','cost.extraCosts','branch','proofOfPickup']);

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
                case 'number':
                    $pickup = $pickup->sortable([
                        'number' => $order
                    ]);
                    break;
                case 'name':
                    $pickup = $pickup->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'receiver.name':
                    $pickup = $pickup->sortable([
                        'receiver.name' => $order
                    ]);
                    break;
                case 'debtor.name':
                    $pickup = $pickup->sortable([
                        'debtor.name' => $order
                    ]);
                    break;
                case 'cost.method':
                    $pickup = $pickup->sortable([
                        'cost.method' => $order
                    ]);
                    break;
                case 'branch.name':
                    $pickup = $pickup->sortable([
                        'branch.name' => $order
                    ]);
                    break;
                case 'created_at':
                    $pickup = $pickup->sortable([
                        'created_at' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'id' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($name)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$name.'%');
        }

        if (!empty($number)) {
            $pickup = $pickup->where('number', 'ilike', '%'.$number.'%');
        }

        if (!empty($receiver)) {
            $pickup = $pickup->whereHas('receiver', function($q) use ($receiver) {
                $q->where('name', 'ilike', '%'.$receiver.'%');
            });
        }

        if (!empty($debtor)) {
            $pickup = $pickup->whereHas('debtor', function($q) use ($debtor) {
                $q->where('name', 'ilike', '%'.$debtor.'%');
            });
        }

        if (!empty($paymentMethod)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($paymentMethod) {
                $q->where('method', 'ilike', '%'.$paymentMethod.'%');
            });
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $pickup = $pickup
                ->whereDate('created_at', '>=', date($dateFrom))
                ->whereDate('created_at', '<=', date($dateTo));
        }

        if (!empty($branchName)) {
            $pickup = $pickup->whereHas('branch', function($q) use ($branchName) {
                $q->where('name', 'ilike', '%'.$branchName.'%');
            });
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * update marketing on order
     */
    public function updateMarketingByOrderId($orderId, $marketingId)
    {
        $pickup = $this->pickup->find($orderId);
        if (!$pickup) {
            throw new InvalidArgumentException('Order tidak ditemukan');
        }
        $pickup->marketing_id = $marketingId;
        $pickup->save();
        return $pickup;
    }

    /**
     * update fleet data
     */
    public function updateFleetDataPickupRepo($data = [])
    {
        $pickup = $this->pickup->find($data['pickupId']);
        if (!$pickup) {
            throw new InvalidArgumentException('Pickup tidak ditemukan');
        }
        $pickup->fleet_name = $data['fleetName'];
        $pickup->fleet_departure = $data['fleetDeparture'];
        $pickup->save();
        return $pickup;
    }
}
