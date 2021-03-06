<?php

namespace App\Repositories;

// MODELS
use App\Models\ProofOfPickup;
use App\Models\Pickup;

// LARAVEL
use DB;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Exception;

// VENDOR
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;

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
            $config = [
                'table' => 'proof_of_pickups',
                'length' => 14,
                'field' => 'number',
                'prefix' => 'POP'.Carbon::now('Asia/Jakarta')->format('ymd'),
                'reset_on_prefix_change' => true
            ];
            $proof = new $this->pop;
            $proof->pickup_id = $data['pickupId'];
            $proof->driver_pick = $data['driverPick'];
            $proof->notes = $data['notes'];
            $proof->created_by = $data['userId'];
            $proof->status_pick = $data['statusPick']; // success, updated, failed
            $proof->number = IdGenerator::generate($config);
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
            $pickup->status = 'applied'; // applied, canceled, request
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
        $branchId = $data['branchId'];

        $pickup = $this->pickup->with(['pickupPlan'])->where(function($e) use ($branchId) {
            $e->where('branch_id', $branchId)->where('status', 'draft')->whereNotNull('pickup_plan_id')->whereHas('proofOfPickup', function ($q) {
                $q->where('driver_pick', false);
            });
        })->orWhere(function($e) use ($branchId) {
            $e->where('branch_id', $branchId)->where('status', 'request')->whereNotNull('pickup_plan_id');
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

        if (!empty($customer)) {
            $pickup = $pickup->where('name', 'ilike', '%'.$customer.'%');
        }

        if (!empty($pickupOrderNo)) {
            $pickup = $pickup->where('number', 'ilike', '%'.$pickupOrderNo.'%');
        }

        if (!empty($requestPickupDate)) {
            $pickup = $pickup->whereDate('picktime', date($requestPickupDate));
        }

        if (!empty($pickupPlanNo)) {
            $pickup = $pickup->whereHas('pickupPlan', function($q) use ($pickupPlanNo) {
                $q->where('number', 'ilike', '%'.$pickupPlanNo.'%');
            });
        }

        if (!empty($general)) {
            $pickup = $pickup
                ->where('name', 'ilike', '%'.$general.'%')
                ->orWhere('number', 'ilike', '%'.$general.'%')
                ->orWhereHas('pickupPlan', function($q) use ($general) {
                    $q->where('number', 'ilike', '%'.$general.'%');
                });
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
        $branchId = $data['branchId'];

        $pickup = $this->pop->with(['pickup' => function($q) {
            $q->whereNotNull('pickup_plan_id');
        },'pickup.pickupPlan']);

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
                    $pickup = $pickup->sortable([
                        'pickup.name' => $order
                    ]);
                    break;
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
                case 'pickup.number':
                    $pickup = $pickup->sortable([
                        'pickup.number' => $order
                    ]);
                    break;
                case 'pickup.picktime':
                    $pickup = $pickup->sortable([
                        'pickup.picktime' => $order
                    ]);
                    break;
                case 'pickup.created_at':
                    $pickup = $pickup->sortable([
                        'pickup.created_at' => $order
                    ]);
                    break;
                case 'status':
                    $pickup = $pickup->sortable([
                        'status' => $order
                    ]);
                    break;
                case 'status_pick':
                    $pickup = $pickup->sortable([
                        'status_pick' => $order
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
            $pickup = $pickup->whereHas('pickup', function($q) use ($poNumber) {
                $q->where('number', 'ilike', '%'.$poNumber.'%');
            });
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
            $pickup = $pickup->whereHas('pickupPlan', function($q) use ($pickupPlanNumber) {
                $q->where('number', 'ilike', '%'.$pickupPlanNumber.'%');
            });
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
                ->orWhere('number', 'ilike', '%'.$general.'%')
                ->orWhereHas('pickupPlan', function($q) use ($general) {
                    $q->where('number', 'ilike', '%'.$general.'%');
                });
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
    public function getPendingAndDraftRepo($branchId)
    {
        $pending = $this->pickup->where('branch_id', $branchId)->whereNotNull('pickup_plan_id')->where('status', 'request')->count();
        $draft = $this->pickup->where('branch_id', $branchId)->whereNotNull('pickup_plan_id')->whereHas('proofOfPickup', function($q) {
            // $q->where('driver_pick', true)->where('status', 'draft');
            $q->where('status', 'draft');
        })->count();
        $data = [
            'pending' => $pending,
            'draft' => $draft
        ];
        return $data;
    }

    /**
     * update pop repo
     */
    public function updatePopRepo($data = [])
    {
        $pop = $this->pop->find($data['proof_of_pickup']['id']);
        if (!$pop) {
            throw new InvalidArgumentException('Proof of pickup tidak ditemukan');
        }
        $pop->status = $data['proof_of_pickup']['status'];
        $pop->status_pick = $data['proof_of_pickup']['status_pick'];
        $pop->notes = $data['proof_of_pickup']['notes'];
        $pop->save();
    }

    /**
     * cancel pop repo
     * @param array $data
     */
    public function cancelPopRepo($data = [])
    {
        $pickup = $this->pickup->find($data['pickupId']);
        if ($pickup->shipment_plan_id !== null) {
            throw new InvalidArgumentException('POP tidak dapat dibatalkan, karena shipment plan pada drop order sudah terbuat');
        }
        $pop = $pickup->proofOfPickup;
        if (!$pop) {
            throw new InvalidArgumentException('Proof of pickup tidak ditemukan');
        }
        $pop->status = 'cancelled';
        $pop->save();
        return $pop;
    }
}
