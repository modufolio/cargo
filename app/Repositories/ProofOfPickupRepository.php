<?php

namespace App\Repositories;

use App\Models\ProofOfPickup;
use App\Models\Pickup;
use Carbon\Carbon;
use DB;
use InvalidArgumentException;
use Exception;

class ProofOfPickupRepository
{
    protected $pop;
    protected $pickup;

    public function __construct(ProofOfPickup $pop, Pickup $pickup)
    {
        $this->pop = $pop;
        $this->pickup = $pickup;
    }

    /**
     * create POP
     *
     * @param array $data
     * @return ProofOfPickup
     */
    public function createPOPRepo($data = [])
    {
        DB::beginTransaction();
        try {
            $proof = new $this->pop;
            $proof->pickup_id = $data['pickupId'];
            $proof->driver_pick = $data['driverPick'];
            $proof->notes = $data['notes'];
            $proof->created_by = $data['userId'];
            if ($data['driverPick']) {
                /**
                 * draft: pop sudah berhasil di submit dari driver app
                 *      dengan apapun jenis status pickupnya
                 */
                $proof->status = 'draft';
            } else {
                /**
                 * applied: pop sudah berhasil di submit dari web yang memiliki status pickup sukses
                 *      atau ada perubahan
                 *
                 * submitted: pop sudah berhasil di submit dari web yang
                 *      memiliki status pickup Gagal, pop dengan doc status Submitted ini
                 *      tidak akan bisa di proses ke tahap shipment plan,
                 *      tapi bisa di masukan ke dalam pickup plan kembali.
                 *
                 * canceled: pop draft atau applied yang sudah dilakukan
                 *      canceled di menu cancel POP dari web
                 *      (akan tampil di tab outstanding dengan status frontend pending)
                 *
                 * pending: status ini untuk pickup order yang belum pernah di submit
                 *      dari web ataupun app driver dan hanya muncul di tab outstanding
                 *      (jangan di simpan di database status ini hanya di frontend saja)
                 */
                $proof->status = $data['popStatus'];
            }
            $proof->save();

        } catch (Exception $e) {
            DB::rollback();
            throw new InvalidArgumentException('Gagal menyimpan data proof of pickup');
        }

        try {
            $pickup = $this->pickup->find($data['pickupId']);
            if (!$pickup) {
                DB::rollback();
                throw new InvalidArgumentException('Pickup tidak ditemukan');
            }
            $pickup->status = $data['status']; // failed, updated, success
            $pickup->save();
        } catch (Exception $e) {
            DB::rollback();
            throw new InvalidArgumentException('Gagal, mengubah status pickup');
        }

        DB::commit();
        return [
            'pop' => $proof,
            'pickup' => $pickup
        ];
    }

    /**
     * get outstanding proof of pickup
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

        $pickup = $this->pickup->where('status', 'request')->whereNotNull('pickup_plan_id');

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
                case 'name':
                    $pickup = $pickup->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'pickup_plan_id':
                    $pickup = $pickup->sortable([
                        'pickup_plan_id' => $order
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

        if (!empty($customer)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$customer.'%');
        }

        if (!empty($pickupOrderNo)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$pickupOrderNo.'%');
        }

        if (!empty($requestPickupDate)) {
            $pickup = $pickup->whereDate('picktime', date($requestPickupDate));
        }

        if (!empty($pickupPlanNo)) {
            $pickup = $pickup->where('pickup_plan_id', 'ilike', '%'.$pickupPlanNo.'%');
        }

        if (!empty($general)) {
            $pickup = $pickup
                ->where('name', 'ilike', '%'.$general.'%')
                ->orWhere('id', 'ilike', '%'.$general.'%')
                ->orWhere('pickup_plan_id', 'ilike', '%'.$general.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get submitted proof of pickup
     * @param array $data
     */
    public function getSubmittedPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $sort = $data['sort'];
        $customer = $data['customer'];
        $poNumber = $data['poNumber'];
        $poPickupDate = $data['poPickupDate'];
        $poStatus = $data['poStatus'];
        $poCreatedDate = $data['poCreatedDate'];
        $pickupPlanNumber = $data['pickupPlanNumber'];
        $popNumber = $data['popNumber'];
        $popDate = $data['popDate'];
        $popStatus = $data['popStatus'];
        $general = $data['general'];
        $driverPick = $data['driverPick'];


        $pickup = $this->pickup->select('id','name','pickup_plan_id','picktime','created_at','status')->where('status', '!=', 'request')->with(['proofOfPickup' => function($q) {
            $q->select('id','pickup_id','status','driver_pick','created_at');
        }])->whereNotNull('pickup_plan_id');

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
                case 'name':
                    $pickup = $pickup->sortable([
                        'name' => $order
                    ]);
                    break;
                case 'id':
                    $pickup = $pickup->sortable([
                        'id' => $order
                    ]);
                    break;
                case 'pickup_plan_id':
                    $pickup = $pickup->sortable([
                        'pickup_plan_id' => $order
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

        if (!empty($customer)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$customer.'%');
        }

        if (!empty($poNumber)) {
            $pickup = $pickup->where('id', 'ilike', '%'.$poNumber.'%');
        }

        if (!empty($poPickupDate)) {
            $pickup = $pickup->whereDate('picktime', date($poPickupDate));
        }

        if (!empty($poStatus)) {
            $pickup = $pickup->where('status', $poStatus);
        }

        if (!empty($poCreatedDate)) {
            $pickup = $pickup->whereDate('created_at', date($poCreatedDate));
        }

        if (!empty($pickupPlanNumber)) {
            $pickup = $pickup->where('pickup_plan_id', 'ilike', '%'.$pickupPlanNumber.'%');
        }

        if (!empty($popNumber)) {
            $pickup = $pickup->whereHas('proofOfPickup', function($q) use ($popNumber) {
                $q->where('id', 'ilike', '%'.$popNumber.'%');
            });
        }

        if (!empty($popDate)) {
            $pickup = $pickup->whereHas('proofOfPickup', function($q) use ($popDate) {
                $q->whereDate('created_at', date($popDate));
            });
        }

        if (!empty($popStatus)) {
            $pickup = $pickup->whereHas('proofOfPickup', function($q) use ($popStatus) {
                $q->where('status', $popStatus);
            });
        }

        if (!empty($driverPick)) {
            $pickup = $pickup->whereHas('proofOfPickup', function($q) use ($driverPick) {
                $q->where('driver_pick', $driverPick);
            });
        }

        if (!empty($general)) {
            $pickup = $pickup
                ->where('name', 'ilike', '%'.$general.'%')
                ->orWhere('id', 'ilike', '%'.$general.'%')
                ->orWhere('pickup_plan_id', 'ilike', '%'.$general.'%');
        }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get pending and draft pickup
     * Counter dashboard ini menampilkan jumlah ada berapa pickup order yang masih pending
     *      (belum di pickup tapi sudah dibuatkan pickup plan)
     *      dan menampilkan jumlah pickup order yang statusnya
     *      DRAFT (pickup order yang sudah di pickup
     *      dan di update via apps driver oleh driver)
     */
    public function getPendingAndDraftRepo()
    {
        $pending = $this->pickup->whereNotNull('pickup_plan_id')->where('status', 'request')->count();
        $draft = $this->pickup->whereNotNull('pickup_plan_id')->whereHas('proofOfPickup', function($q) {
            $q->where('driver_pick', true)->where('status', 'draft');
        })->count();
        $data = [
            'pending' => $pending,
            'draft' => $draft
        ];
        return $data;
    }
}
