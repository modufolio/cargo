<?php
namespace App\Services;

use App\Repositories\ItemRepository;
use Exception;
use DB;
use Log;
use Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ItemService {

    protected $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * update item service
     *
     * @param array $data
     * @return String
     */
    public function updateItemService($data = [])
    {
        $validator = Validator::make($data, [
            'itemId' => 'bail|required',
            'name' => 'bail|required',
            'total' => 'bail|required',
            'count' => 'bail|required',
            'serviceId' => 'bail|present',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $result = $this->itemRepository->updateItemRepo($data);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        DB::commit();
        return $result;
    }

    /**
     * get item by pickup service
     *
     * @param array $data
     * @return String
     */
    public function fetchItemByPickupService($data = [])
    {
        $validator = Validator::make($data, [
            'pickupId' => 'bail|required',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        try {
            $result = $this->itemRepository->fetchItemByPickupRepo($data);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw new InvalidArgumentException($e->getMessage());
        }
        return $result;
    }
}
