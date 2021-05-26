<?php

namespace App\Repositories;

// MODELS
use App\Models\Pickup;
use App\Models\ExtraCost;

// OTHER
use InvalidArgumentException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RouteImport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Validators\ValidationException;
use DB;

class ReportRepository
{
    protected $pickup;
    protected $extraCost;

    public function __construct(Pickup $pickup, ExtraCost $extraCost)
    {
        $this->pickup = $pickup;
        $this->extraCost = $extraCost;
    }

    /**
     * get report success order with range
     *
     * @param array $data
     */
    public function getReportSuccessOrderRepo($data)
    {
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $result = $this->pickup
            ->whereHas('proofOfDelivery', function($q) {
                $q->where('status', 'applied')->where('status_delivery', 'success');
            })
            ->whereDate('created_at', '>=', date($startDate))
            ->whereDate('created_at', '<=', date($endDate))
            ->get();
        return $result;
    }

    /**
     * get reporting
     */
    public function getReportRepo($data = [])
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
        $marketingName = $data['marketingName'];

        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $driverPickupName = $data['driverPickupName'];
        $driverDeliveryName = $data['driverDeliveryName'];

        $costAmountWithService = $data['costAmountWithService'];
        $costDiscount = $data['costDiscount'];
        $costAmount = $data['costAmount'];
        $costService = $data['costService'];
        $costExtraCost = $data['costExtraCost'];
        $costMargin = $data['costMargin'];
        $costMethod = $data['costMethod'];
        $costStatus = $data['costStatus'];

        $pickup = $this->pickup->select('id','number','name','user_id','receiver_id','sender_id','debtor_id','branch_id','marketing_id','picktime','created_at','pickup_plan_id','shipment_plan_id')
            // ->whereNotNull('pickup_plan_id')
            // ->whereNotNull('delivery_plan_id')
            ->with(['user' => function($q) {
                $q->select('id','name');
            },'receiver' => function($q) {
                $q->select('id','name');
            },'debtor' => function($q) {
                $q->select('id','name');
            },'cost' => function($q) {
                $q->select('id','method','pickup_id','amount','discount','service','clear_amount','status','amount_with_service');
            },'marketing' => function($q) {
                $q->select('id','name');
            },'branch' => function($q) {
                $q->select('id','name');
            },'pickupPlan.vehicle.driver.user' => function($q) {
                $q->select('id','name');
            },'pickupPlan.vehicle' => function($q) {
                $q->select('id','driver_id');
            },'pickupPlan.vehicle.driver' => function($q) {
                $q->select('id','user_id');
            },'pickupPlan' => function($q) {
                $q->select('id','vehicle_id');
            },'shipmentPlan.vehicle.driver.user' => function($q) {
                $q->select('id','name');
            },'shipmentPlan.vehicle' => function($q) {
                $q->select('id','driver_id');
            },'shipmentPlan.vehicle.driver' => function($q) {
                $q->select('id','user_id');
            },'shipmentPlan' => function($q) {
                $q->select('id','vehicle_id');
            }]);

        if (empty($perPage)) {
            $perPage = 99999999;
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
                case 'created_at':
                    $pickup = $pickup->sortable([
                        'created_at' => $order
                    ]);
                    break;
                case 'picktime':
                    $pickup = $pickup->sortable([
                        'picktime' => $order
                    ]);
                    break;
                case 'branch.name':
                    $pickup = $pickup->sortable([
                        'branch.name' => $order
                    ]);
                    break;
                case 'marketing.name':
                    $pickup = $pickup->sortable([
                        'marketing.name' => $order
                    ]);
                    break;
                case 'cost.amount_with_service':
                    $pickup = $pickup->sortable([
                        'cost.amount_with_service' => $order
                    ]);
                    break;
                case 'cost.discount':
                    $pickup = $pickup->sortable([
                        'cost.discount' => $order
                    ]);
                    break;
                case 'cost.amount':
                    $pickup = $pickup->sortable([
                        'cost.amount' => $order
                    ]);
                    break;
                case 'cost.service':
                    $pickup = $pickup->sortable([
                        'cost.service' => $order
                    ]);
                    break;
                case 'cost.method':
                    $pickup = $pickup->sortable([
                        'cost.method' => $order
                    ]);
                    break;
                case 'cost.status':
                    $pickup = $pickup->sortable([
                        'cost.status' => $order
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

        if (!empty($startDate) && !empty($endDate)) {
            $pickup = $pickup
                ->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateTimeString())
                ->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateTimeString());
        }

        if (!empty($branchName)) {
            $pickup = $pickup->whereHas('branch', function($q) use ($branchName) {
                $q->where('name', 'ilike', '%'.$branchName.'%');
            });
        }

        if (!empty($marketingName)) {
            $pickup = $pickup->whereHas('marketing', function($q) use ($marketingName) {
                $q->where('name', 'ilike', '%'.$marketingName.'%');
            });
        }

        if (!empty($driverPickupName)) {
            $pickup = $pickup->whereHas('pickupPlan', function($q) use ($driverPickupName) {
                $q->whereHas('vehicle', function($q) use ($driverPickupName) {
                    $q->whereHas('driver', function($q) use ($driverPickupName) {
                        $q->whereHas('user', function($q) use ($driverPickupName) {
                            $q->where('name', 'ilike', '%'.$driverPickupName.'%');
                        });
                    });
                });
            });
        }

        if (!empty($driverDeliveryName)) {
            $pickup = $pickup->whereHas('shipmentPlan', function($q) use ($driverDeliveryName) {
                $q->whereHas('vehicle', function($q) use ($driverDeliveryName) {
                    $q->whereHas('driver', function($q) use ($driverDeliveryName) {
                        $q->whereHas('user', function($q) use ($driverDeliveryName) {
                            $q->where('name', 'ilike', '%'.$driverDeliveryName.'%');
                        });
                    });
                });
            });
        }

        if (!empty($costAmountWithService)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costAmountWithService) {
                $q->where('amount_with_service', $costAmountWithService);
            });
        }

        if (!empty($costDiscount)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costDiscount) {
                $q->where('discount', $costDiscount);
            });
        }

        if (!empty($costAmount)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costAmount) {
                $q->where('amount', $costAmount);
            });
        }

        if (!empty($costService)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costService) {
                $q->where('service', $costService);
            });
        }

        if (!empty($costExtraCost)) {
            // $pickup = $pickup->withCount(['extraCostsCount as extra_cost_count_data' => function($q) {
            //     $q->selectRaw('sum(extra_costs.amount) as amount_extra_costs');
            //     // ->groupBy('extra_costs.amount')
            //     // ->whereRaw('extra_costs.amount = ?', [$costExtraCost]);
            // }])->whereHas('extraCostsCount', function($q) {
            //     $q->where('');
            // });

            // $pickup = $pickup->with(['extraCosts' => function ($query) {
            //     $query->selectRaw('SUM(extra_costs.amount) as extraCost_sum')->whereRaw('extraCost_sum = ?', [10]);
            // }]);

            // $pickup = $pickup->havingRaw('sum(extra_costs.amount) = ?', [100000]);

            // $pickup = $pickup->whereHas('cost' ,function($q) use ($costExtraCost) {
            //     $q->whereHas('extraCosts', function($q) use ($costExtraCost) {
            //             $q->havingRaw('SUM(extra_costs.amount) = ?', [$costExtraCost])
            //                 ->groupBy('id');
            //     });
            // });

            // extraCost->where('cost_id', $this->id)->get()->all();
            // if (count($extraCosts) > 0) {
            //     $total = array_sum(array_column($extraCosts, 'amount'));
            // } else {
            //     $total = 0;
            // }
            // $pickup = $pickup->whereHas('cost', function($q) use ($costExtraCost) {
            //     $q->whereRaw('total_extra_cost', $costExtraCost);
            // });
        }

        if (!empty($costMargin)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costMargin) {
                $q->whereRaw('margin', $costMargin);
            });
        }

        if (!empty($costMethod)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costMethod) {
                $q->where('method', 'ilike', '%'.$costMethod.'%');
            });
        }

        if (!empty($costStatus)) {
            $pickup = $pickup->whereHas('cost', function($q) use ($costStatus) {
                $q->where('status', 'ilike', '%'.$costStatus.'%');
            });
        }

        $result = $pickup->simplePaginate($perPage);

        return $result;
    }
}
