<?php

namespace App\Repositories;

// MODEL
use App\Models\ProofOfDelivery;
use App\Models\Pickup;

// OTHER
use DB;
use InvalidArgumentException;
use Exception;
use Illuminate\Database\Eloquent\Builder;

// VENDOR
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;

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
        // $general = $data['general'];
        $pickupOrderNo = $data['pickupOrderNo'];
        $shipmentPlanNumber = $data['shipmentPlanNumber'];
        $branchId = $data['branchId'];

        $pickup = $this->pickup
            ->with(['shipmentPlan' => function($q) {
                $q->where('status', 'applied');
            }])
            ->has('shipmentPlan')
            ->where('pickups.status', 'applied')
            ->where('branch_id', $branchId)
            ->where('is_transit', false)
            ->whereHas('proofOfPickup', function($e) {
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

        if (!empty($shipmentPlanNumber)) {
            $pickup = $pickup->whereHas('shipmentPlan', function($q) use ($shipmentPlanNumber) {
                $q->where('number', 'ilike', '%'.$shipmentPlanNumber.'%');
            });
        }

        // if (!empty($general)) {
        //     $pickup = $pickup
        //         ->whereHas('shipmentPlan', function($q) use ($general) {
        //             $q->where('number', 'ilike', '%'.$general.'%');
        //         })
        //         ->orWhere('name', 'ilike', '%'.$general.'%')
        //         ->orWhere('number', 'ilike', '%'.$general.'%');
        // }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get submitted proof of delivery
     * @param array $data
     */
    public function getSubmittedPickupRepo($data = [])
    {
        $perPage = $data['perPage'];
        $page = $data['page'];
        $sort = $data['sort'];
        $branchId = $data['branchId'];
        $customer = $data['customer'];
        $pickupOrderNo = $data['pickupOrderNo'];
        $shipmentPlanNumber = $data['shipmentPlanNumber'];
        $podNumber = $data['podNumber'];
        $statusDelivery = $data['statusDelivery'];
        $podStatus = $data['podStatus'];

        $pickup = $this->pickup->select('id','name','number','shipment_plan_id')
            ->where('pickups.status', 'applied')
            ->where('branch_id', $branchId)
            ->whereNotNull('shipment_plan_id')
            ->has('proofOfDelivery')
            ->where('is_transit', false)
            ->with([
                'proofOfDelivery' => function($q) {
                    $q->select('id','pickup_id','status','notes','status_delivery','created_at','number','redelivery_count');
                }, 'shipmentPlan' => function($q) {
                    $q->select('id', 'number');
                }]);

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
                case 'number':
                    $pickup = $pickup->sortable([
                        'number' => $order
                    ]);
                    break;
                case 'shipment_plan.number':
                    $pickup = $pickup->sortable([
                        'shipmentPlan.number' => $order
                    ]);
                    break;
                case 'proof_of_delivery.number':
                    $pickup = $pickup->sortable([
                        'proofOfDelivery.number' => $order
                    ]);
                    break;
                case 'proof_of_delivery.status_delivery':
                    $pickup = $pickup->sortable([
                        'proofOfDelivery.status_delivery' => $order
                    ]);
                    break;
                case 'proof_of_delivery.redelivery_count':
                    $pickup = $pickup->sortable([
                        'proofOfDelivery.redelivery_count' => $order
                    ]);
                    break;
                default:
                    $pickup = $pickup->sortable([
                        'proofOfDelivery.updated_at' => 'desc'
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

        if (!empty($shipmentPlanNumber)) {
            $pickup = $pickup->whereHas('shipmentPlan', function($q) use ($shipmentPlanNumber) {
                $q->where('number', 'ilike', '%'.$shipmentPlanNumber.'%');
            });
        }

        if (!empty($podNumber)) {
            $pickup = $pickup->whereHas('proofOfDelivery', function($q) use ($podNumber) {
                $q->where('number', 'ilike', '%'.$podNumber.'%');
            });
        }

        if (!empty($statusDelivery)) {
            $pickup = $pickup->whereHas('proofOfDelivery', function($q) use ($statusDelivery) {
                $q->whereIn('proof_of_deliveries.status_delivery', $statusDelivery);
            });
        }

        if (!empty($podStatus)) {
            $pickup = $pickup->whereHas('proofOfDelivery', function($q) use ($podStatus) {
                $q->whereIn('proof_of_deliveries.status', $podStatus);
            });
        }

        // if (!empty($driverPick)) {
        //     $pickup = $pickup->whereHas('proofOfPickup', function($q) use ($driverPick) {
        //         $q->where('driver_pick', $driverPick);
        //     });
        // }

        // if (!empty($general)) {
        //     $pickup = $pickup
        //         ->where('name', 'ilike', '%'.$general.'%')
        //         ->orWhere('number', 'ilike', '%'.$general.'%')
        //         ->orWhereHas('pickupPlan', function($q) use ($general) {
        //             $q->where('number', 'ilike', '%'.$general.'%');
        //         });
        // }

        $result = $pickup->paginate($perPage);

        return $result;
    }

    /**
     * get pending and draft POD
     */
    public function getPendingAndDraftRepo($request)
    {
        $branchId = $request->branchId;
        $pending = $this->pickup
            ->with(['shipmentPlan' => function($q) {
                $q->where('status', 'applied');
            }])
            ->has('shipmentPlan')
            ->where('pickups.status', 'applied')
            ->where('branch_id', $branchId)
            ->where('is_transit', false)
            ->whereHas('proofOfPickup', function($e) {
                $e->where('status', 'applied');
            })
            ->doesntHave('proofOfDelivery')
            ->count();
        $draft = $this->pickup
            ->where('status', 'applied')
            ->where('branch_id', $branchId)
            ->whereNotNull('pickup_plan_id')
            ->whereNotNull('shipment_plan_id')
            ->whereHas('proofOfDelivery', function($q) {
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
    public function submitPODRepo($data = [])
    {
        $config = [
            'table' => 'proof_of_deliveries',
            'length' => 13,
            'field' => 'number',
            'prefix' => 'PD'.Carbon::now('Asia/Jakarta')->format('ymd'),
            'reset_on_prefix_change' => true
        ];
        $pod = new $this->pod;
        $pod->pickup_id = $data['pickupId'];
        $pod->status_delivery = $data['statusDelivery'];
        $pod->status = $data['status'];
        $pod->notes = $data['notes'];
        $pod->number = IdGenerator::generate($config);
        $pod->created_by = $data['userId'];
        $pod->updated_by = $data['userId'];
        $pod->redelivery_count = $data['totalRedelivery'];
        $pod->save();
        return $pod;
    }

    /**
     * get total redelivery of pod pickup
     */
    public function getTotalRedelivery($data = [])
    {
        $result = $this->pod->where('pickup_id', $data['pickupId'])->select('redelivery_count')->first();
        if (!$result) {
            return 0;
        }
        return $result->redelivery_count;
    }
}
