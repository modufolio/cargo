<?php
namespace App\Services;

use App\Models\Pickup;
use App\Repositories\PickupRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class PickupService {

    protected $pickupRepository;

    public function __construct(PickupRepository $pickupRepository)
    {
        $this->pickupRepository = $pickupRepository;
    }

    /**
     * Validate pickup data.
     * Store to DB if there are no errors.
     *
     * @param Address $address
     * @param user_id $userId
     * @param fleet_id $fleetId
     * @return mixed
     */
    public function save($address, $userId, $fleetId)
    {
        $validator = Validator::make($address, [
            'fleetId'           => 'bail|required|max:19',
            'userId'            => 'bail|required|max:19',
            'promoId'           => 'bail|nullable|max:19',
            'name'              => 'bail|required|max:255',
            'phone'             => 'bail|required|max:14',
            'addressSender'    => 'bail|required|max:500',
            'addressRecepient' => 'bail|required|max:500',
            'addressBilling'   => 'bail|required|max:500',
            'notes'             => 'bail|required|max:500',
            'picktime'          => 'bail|date'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->pickupRepository->save($address, $userId, $fleetId);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal menyimpan alamat pickup');
        }
        DB::commit();
        return $result;
    }
}
