<?php

namespace App\Repositories;

use App\Models\ProofOfDelivery;
use App\Models\Pickup;
use Carbon\Carbon;
use DB;
use InvalidArgumentException;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class ProofOfDeliveryRepository
{
    protected $pod;
    protected $pickup;

    public function __construct(ProofOfDelivery $pod, Pickup $pickup)
    {
        $this->pod = $pod;
        $this->pickup = $pickup;
    }

    /**
     * create POD
     *
     * @param array $data
     * @return ProofOfDelivery
     */
    public function createPOPRepo($data = [])
    {
        DB::beginTransaction();
        try {
            $proof = new $this->pod;
            $proof->pickup_id = $data['pickupId'];
            $proof->driver_pick = $data['driverPick'];
            $proof->notes = $data['notes'];
            $proof->created_by = $data['userId'];
            $proof->status_pick = $data['statusPick']; // success, updated, failed
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
                 *      canceled di menu cancel POD dari web
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
     * get outstanding proof of delivery
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
        $shipmentPlanNumber = $data['shipmentPlanNumber'];
        $branchId = $data['branchId'];

        $pickup = $this->pickup
            ->whereNotNull('shipment_plan_id')
            ->where('branch_id', $branchId)
            ->where('is_transit', false)
            ->with(['shipmentPlan'])
            ->whereHas('shipmentPlan', function($e) {
                $e->where('status', 'applied');
            })
            ->doesntHave('proofOfDelivery');

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
                case 'shipment_plan.number':
                    $pickup = $pickup->sortable([
                        'shipmentPlan.number' => $order
                    ]);
                    break;
                case 'shipment_plan.created_at':
                    $pickup = $pickup->sortable([
                        'shipmentPlan.created_at' => $order
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

        if (!empty($shipmentPlanNumber)) {
            $pickup = $pickup->whereHas('shipmentPlan', function($q) use ($shipmentPlanNumber) {
                $q->where('number', 'ilike', '%'.$shipmentPlanNumber.'%');
            });
        }

        if (!empty($general)) {
            $pickup = $pickup
                ->where('name', 'ilike', '%'.$general.'%')
                ->orWhere('number', 'ilike', '%'.$general.'%')
                ->orWhereHas('shipmentPlan', function($q) use ($general) {
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


        $pickup = $this->pickup->select('id','name','pickup_plan_id','picktime','created_at','status','number')->where('status', 'applied')->with([
            'proofOfPickup' => function($q) {
                $q->select('id','pickup_id','status','driver_pick','status_pick','created_at');
            }, 'pickupPlan' => function($q) {
                $q->select('id', 'number');
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
            $pickup = $pickup->where('number', 'ilike', '%'.$poNumber.'%');
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
    public function getPendingAndDraftRepo()
    {
        $pending = $this->pickup->whereNotNull('pickup_plan_id')->where('status', 'request')->count();
        $draft = $this->pickup->whereNotNull('pickup_plan_id')->whereHas('proofOfPickup', function($q) {
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
        $pod = $this->pod->find($data['proof_of_pickup']['id']);
        if (!$pod) {
            throw new InvalidArgumentException('Proof of pickup tidak ditemukan');
        }
        $pod->status = $data['proof_of_pickup']['status'];
        $pod->status_pick = $data['proof_of_pickup']['status_pick'];
        $pod->notes = $data['proof_of_pickup']['notes'];
        $pod->save();
    }

    /**
     * get detail pickup order for web
     * @param array $data
     */
    public function getDetailPickupAdminRepo($data = [])
    {
        $pickup = $this->pickup->select('id','name','phone','picktime','sender_id','receiver_id','shipment_plan_id','status','number')->where('id', $data['pickupId'])->with(
            [
                'sender' => function($q) {
                    $q->select('id', 'province','city','district','village','postal_code','street');
                },
                'items' => function($q) {
                    $q->select('id','name','pickup_id','unit_count','service_id','weight','volume','type','price');
                },
                'cost',
                'items.service' => function($q) {
                    $q->select('id','name');
                },
                'shipmentPlan' => function($q) {
                    $q->select('id','vehicle_id','number');
                },
                'shipmentPlan.vehicle' => function($q) {
                    $q->select('id','driver_id');
                },
                'shipmentPlan.vehicle.driver' => function($q) {
                    $q->select('id','user_id');
                },
                'shipmentPlan.vehicle.driver.user' => function($q) {
                    $q->select('id','name');
                },
                'proofOfDelivery' => function($q) {
                    $q->select('id', 'pickup_id', 'notes', 'status', 'status_delivery');
                }
            ])->first();

        if (!$pickup) {
            throw new InvalidArgumentException('Maaf, pickup order tidak ditemukan');
        }
        return $pickup;
    }

    /**
     * update status delivery pod
     */
    public function updateStatusDeliveryPODRepo($data = [])
    {
        $result = $this->pod->updateOrCreate(
            [
                'pickup_id' => $data['pickupId'],
                'created_by' => $data['userId']
            ],
            [
                'status_delivery' => $data['statusDelivery'],
                'updated_by' => $data['userId'],
                'status' => $data['status'],
                'notes' => $data['notes']
            ]
        );
        return $result;
    }
}
