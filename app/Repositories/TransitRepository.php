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
            'length' => 1,
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
        $requestPickupDate = $data['requestPickupDate'];
        $pickupPlanNo = $data['pickupPlanNo'];

        $transit = $this->transit->with('pickup')->where('status', 'pending')->where('received', false);

        if (empty($perPage)) {
            $perPage = 10;
        }

        // if (!empty($sort['field'])) {
        //     $order = $sort['order'];
        //     if ($order == 'ascend') {
        //         $order = 'asc';
        //     } else if ($order == 'descend') {
        //         $order = 'desc';
        //     } else {
        //         $order = 'desc';
        //     }
        //     switch ($sort['field']) {
        //         case 'name':
        //             $transit = $transit->sortable([
        //                 'name' => $order
        //             ]);
        //             break;
        //         case 'id':
        //             $transit = $transit->sortable([
        //                 'id' => $order
        //             ]);
        //             break;
        //         case 'pickup_plan_id':
        //             $transit = $transit->sortable([
        //                 'pickup_plan_id' => $order
        //             ]);
        //             break;
        //         case 'picktime':
        //             $transit = $transit->sortable([
        //                 'picktime' => $order
        //             ]);
        //             break;
        //         case 'number':
        //             $transit = $transit->sortable([
        //                 'number' => $order
        //             ]);
        //             break;
        //         default:
        //             $transit = $transit->sortable([
        //                 'number' => 'desc'
        //             ]);
        //             break;
        //     }
        // }

        // if (!empty($customer)) {
        //     $transit = $transit->where('name', 'ilike', '%'.$customer.'%');
        // }

        // if (!empty($pickupOrderNo)) {
        //     $transit = $transit->where('number', 'ilike', '%'.$pickupOrderNo.'%');
        // }

        // if (!empty($requestPickupDate)) {
        //     $transit = $transit->whereDate('picktime', date($requestPickupDate));
        // }

        // if (!empty($pickupPlanNo)) {
        //     $transit = $transit->whereHas('pickupPlan', function($q) use ($pickupPlanNo) {
        //         $q->where('number', 'ilike', '%'.$pickupPlanNo.'%');
        //     });
        // }

        // if (!empty($general)) {
        //     $transit = $transit
        //         ->where('name', 'ilike', '%'.$general.'%')
        //         ->orWhere('number', 'ilike', '%'.$general.'%')
        //         ->orWhereHas('pickupPlan', function($q) use ($general) {
        //             $q->where('number', 'ilike', '%'.$general.'%');
        //         });
        // }

        $result = $transit->paginate($perPage);

        return $result;
    }
}
