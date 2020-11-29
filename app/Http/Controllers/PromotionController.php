<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PromotionService;
use App\Http\Requests\PromotionRequest;
use App\Http\Controllers\BaseController;

class PromotionController extends BaseController
{
    public function create(PromotionRequest $request, PromotionService $service)
    {
       $promo = $service->handle($request->all());
       return $this->sendResponse($promo, 'Create promotion successfully');
    }
}
