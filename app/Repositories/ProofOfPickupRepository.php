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
            $proof->status = 'applied';
            $proof->pickup_id = $data['pickupId'];
            $proof->driver_pick = $data['driverPick'];
            $proof->notes = $data['notes'];
            $proof->created_by = $data['userId'];
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
            $pickup->status = $data['status'];
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
}
