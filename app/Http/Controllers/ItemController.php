<?php

namespace App\Http\Controllers;

// SERVICE
use App\Services\ItemService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class ItemController extends BaseController
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * update item of pickup
     */
    public function update(Request $request)
    {
        $data = $request->only([
            'itemId',
            'name',
            'total',
            'count',
            'serviceId'
        ]);
        try {
            $result = $this->itemService->updateItemService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
