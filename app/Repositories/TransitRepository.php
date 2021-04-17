<?php

namespace App\Repositories;

use App\Models\Transit;
use Carbon\Carbon;
use InvalidArgumentException;

class TransitRepository
{
    protected $transit;

    public function __construct(Transit $transit)
    {
        $this->transit = $transit;
    }

    /**
     * save transit
     *
     * @param array $data
     * @return Transit
     */
    public function saveTransitRepo($data = [])
    {
        $transit = $this->transit;
        $transit->pickup_id = $data['pickupId'];
        $transit->from = $data['branchFrom'];
        $transit->to = $data['branchTo'];
        $transit->save();
        return $transit;
    }
}
