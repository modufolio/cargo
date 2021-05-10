<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

// SERVICE
use App\Services\ServiceService;

class ServiceController extends BaseController
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index()
    {
        try {
            $result = $this->serviceService->getAll();
        } catch (Exception $th) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get service paginate.
     *
     */
    public function getPaginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'name',
            'price',
            'sort'
        ]);
        try {
            $result = $this->serviceService->getPaginateService($data);
        } catch (Exception $th) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * create service.
     *
     */
    public function create(Request $request)
    {
        $data = $request->only([
            'name',
            'price',
        ]);
        try {
            $result = $this->serviceService->createService($data);
        } catch (Exception $th) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * update service.
     *
     */
    public function update(Request $request)
    {
        $data = $request->only([
            'name',
            'price',
            'id'
        ]);
        try {
            $result = $this->serviceService->updateService($data);
        } catch (Exception $th) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * delete service.
     *
     */
    public function delete(Request $request)
    {
        $data = $request->only([
            'serviceId'
        ]);
        try {
            $result = $this->serviceService->deleteService($data);
        } catch (Exception $th) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
