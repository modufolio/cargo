<?php
namespace App\Services;

use App\Models\Pickup;
use App\Repositories\PickupRepository;
use App\Repositories\ItemRepository;
use Exception;
use DB;
use Log;
use Validator;
use InvalidArgumentException;

class PickupService {

    protected $pickupRepository;
    protected $itemRepository;

    public function __construct(PickupRepository $pickupRepository, ItemRepository $itemRepository)
    {
        $this->pickupRepository = $pickupRepository;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Validate pickup data.
     * Store to DB if there are no errors.
     *
     * @param data
     * @return mixed
     */
    public function save($data)
    {
        $validator = Validator::make($data, [
            'fleetId'           => 'bail|required|max:19',
            'userId'            => 'bail|required|max:19',
            'promoId'           => 'bail|nullable|max:19',
            'name'              => 'bail|required|max:255',
            'phone'             => 'bail|required|max:14',
            'addressSender'     => 'bail|required|max:500',
            'addressReceiver'   => 'bail|required|max:500',
            'addressBilling'    => 'bail|required|max:500',
            'notes'             => 'bail|required|max:500',
            'picktime'          => 'bail|date',
            'items'             => 'bail|required|array'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // dd($data['items']);

        DB::beginTransaction();
        try {
            $pickup = $this->pickupRepository->save($data);
            // $item = $this->itemRepository->save($pickup, $data['items']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Gagal membuat permintaan pickup');
        }
        DB::commit();
        return $pickup;
    }
}
