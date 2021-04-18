<?php

namespace App\Repositories;

use App\Models\Transit;
use App\Models\Pickup;

use Carbon\Carbon;
use InvalidArgumentException;

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
        $transit = $this->transit;
        $transit->pickup_id = $data['pickupId'];
        $transit->status = $data['status'];
        $transit->received = $data['received'];
        $transit->notes = $data['notes'];
        $transit->created_by = $data['userId'];
        $transit->updated_by = $data['userId'];
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
}
