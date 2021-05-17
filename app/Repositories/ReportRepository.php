<?php

namespace App\Repositories;

// MODELS
use App\Models\Pickup;

// OTHER
use InvalidArgumentException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RouteImport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Validators\ValidationException;

class ReportRepository
{
    protected $pickup;

    public function __construct(Pickup $pickup)
    {
        $this->pickup = $pickup;
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
}
