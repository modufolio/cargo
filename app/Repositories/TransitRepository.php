<?php

namespace App\Repositories;

use App\Models\Transit;
use App\Models\Pickup;

use Carbon\Carbon;
use InvalidArgumentException;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class TransitRepository
{
    protected $transit;
    protected $pickup;

    public function __construct(Transit $transit, Pickup $pickup)
    {
        $this->transit = $transit;
        $this->pickup = $pickup;
    }

    /**
     * save transit
     *
     * @param array $data
     * @return Transit
     */
    public function saveTransitRepo($data = [])
    {
        $config = [
            'table' => 'transits',
            'length' => 12,
            'field' => 'number',
            'prefix' => 'T'.Carbon::now('Asia/Jakarta')->format('ymd'),
            'reset_on_prefix_change' => true
        ];
        $transit = $this->transit;
        $transit->pickup_id = $data['pickupId'];
        $transit->status = $data['status'];
        $transit->received = $data['received'];
        $transit->notes = $data['notes'];
        $transit->created_by = $data['userId'];
        $transit->updated_by = $data['userId'];
        $transit->number = IdGenerator::generate($config);
        $transit->save();
        return $transit;
    }

    /**
     * get pending and draft transit pickup
     * Counter dashboard ini menampilkan jumlah ada berapa pickup order yang masih pending transit
     *      (belum di pickup tapi sudah di transit)
     *      dan menampilkan jumlah pickup order yang statusnya
     *      DRAFT (pickup order yang sudah di pickup
     *      dan di update via apps driver oleh driver)
     */
    public function getPendingAndDraftRepo()
    {
        $transits = collect($this->transit->all());
        $pending = $transits->where('status', 'pending')->count();
        $draft = $transits->where('status', 'draft')->count();
        $data = [
            'pending' => $pending,
            'draft' => $draft
        ];
        return $data;
    }

    /**
     * get outstanding transit pickup
     * status only pending and received false
     * @param array $data
     */
    public function getOutstandingPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $sort = $data['sort'];
        $customer = $data['customer'];
        $general = $data['general'];
        $pickupOrderNo = $data['pickupOrderNo'];
        $transitNumber = $data['transitNumber'];

        $transit = $this->transit->with('pickup')->where('transits.status', 'pending')->where('received', false);

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
                case 'pickup.name':
                    $transit = $transit->sortable([
                        'pickup.name' => $order
                    ]);
                    break;
                case 'pickup.number':
                    $transit = $transit->sortable([
                        'pickup.number' => $order
                    ]);
                    break;
                case 'number':
                    $transit = $transit->sortable([
                        'number' => $order
                    ]);
                    break;
                case 'created_at':
                    $transit = $transit->sortable([
                        'created_at' => $order
                    ]);
                    break;
                default:
                    $transit = $transit->sortable([
                        'updated_at' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($customer)) {
            $transit = $transit->whereHas('pickup', function($q) use ($customer) {
                $q->where('name', 'ilike', '%'.$customer.'%');
            });
        }

        if (!empty($transitNumber)) {
            $transit = $transit->where('number', 'ilike', '%'.$transitNumber.'%');
        }

        if (!empty($pickupOrderNo)) {
            $transit = $transit->whereHas('pickup', function($q) use ($pickupOrderNo) {
                $q->where('number', 'ilike', '%'.$pickupOrderNo.'%');
            });
        }

        if (!empty($general)) {
            $transit = $transit
                ->where('number', 'ilike', '%'.$general.'%')
                ->orWhereHas('pickup', function($q) use ($general) {
                    $q->where('name', 'ilike', '%'.$general.'%');
                })
                ->orWhereHas('pickup', function($q) use ($general) {
                    $q->where('number', 'ilike', '%'.$general.'%');
                });
        }

        $result = $transit->paginate($perPage);

        return $result;
    }

     /**
     * draft transit
     *
     * @param array $data
     * @return Tracking
     */
    public function draftTransitRepo($data = [])
    {
        $transit = $this->transit->find($data['transitId']);
        $transit->status = $data['status'];
        $transit->received = $data['received'];
        $transit->notes = $data['notes'];
        $transit->updated_by = $data['userId'];
        $transit->save();
        return $transit;
    }

    /**
     * get submitted transit
     * @param array $data
     */
    public function getSubmittedPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $sort = $data['sort'];
        $customer = $data['customer'];
        $general = $data['general'];
        $pickupOrderNo = $data['pickupOrderNo'];
        $transitNumber = $data['transitNumber'];

        $transit = $this->transit->with(['pickup','pickup.receiver'])
            ->where('received', true)
            ->where('transits.status', 'draft')->orWhere('transits.status', 'applied');

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
                case 'pickup.name':
                    $transit = $transit->sortable([
                        'pickup.name' => $order
                    ]);
                    break;
                case 'pickup.number':
                    $transit = $transit->sortable([
                        'pickup.number' => $order
                    ]);
                    break;
                case 'number':
                    $transit = $transit->sortable([
                        'number' => $order
                    ]);
                    break;
                case 'created_at':
                    $transit = $transit->sortable([
                        'created_at' => $order
                    ]);
                    break;
                default:
                    $transit = $transit->sortable([
                        'updated_at' => 'desc'
                    ]);
                    break;
            }
        }

        if (!empty($customer)) {
            $transit = $transit->whereHas('pickup', function($q) use ($customer) {
                $q->where('name', 'ilike', '%'.$customer.'%');
            });
        }

        if (!empty($transitNumber)) {
            $transit = $transit->where('number', 'ilike', '%'.$transitNumber.'%');
        }

        if (!empty($pickupOrderNo)) {
            $transit = $transit->whereHas('pickup', function($q) use ($pickupOrderNo) {
                $q->where('number', 'ilike', '%'.$pickupOrderNo.'%');
            });
        }

        if (!empty($general)) {
            $transit = $transit
                ->where('number', 'ilike', '%'.$general.'%')
                ->orWhereHas('pickup', function($q) use ($general) {
                    $q->where('name', 'ilike', '%'.$general.'%');
                })
                ->orWhereHas('pickup', function($q) use ($general) {
                    $q->where('number', 'ilike', '%'.$general.'%');
                });
        }

        $result = $transit->paginate($perPage);

        return $result;
    }
}
