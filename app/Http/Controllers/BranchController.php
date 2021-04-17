<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Exception;

use App\Services\BranchService;
use App\Http\Controllers\BaseController;


class BranchController extends BaseController
{
    /**
     * @var userService
     */
    protected $branchService;

    /**
     * BranchController Constructor
     *
     * @param BranchService $branchService
     *
     */
    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * Display a listing of the resource paginate.
     *
     * @return Branch with paginate
     * @param Request $request
     */
    public function paginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'name',
            'city',
            'district',
            'province',
            'id',
            'sort'
        ]);
        try {
            $result = $this->branchService->getAllPaginate($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Branch
     * @param Request $request
     */
    public function list(Request $request)
    {
        try {
            $result = $this->branchService->getAllBranchService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * Delete branch
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $data = $request->only([
            'branchId'
        ]);
        try {
            $result = $this->branchService->deleteBranchService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * create branch.
     *
     * @return Branch
     * @param Request $request
     */
    public function create(Request $request)
    {
        $data = $request->only([
            'name',
            'city',
            'district',
            'province',
            'village',
            'postalCode',
            'street'
        ]);
        try {
            $result = $this->branchService->createBranchService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * update branch.
     *
     * @return Branch
     * @param Request $request
     */
    public function update(Request $request)
    {
        $data = $request->only([
            'name',
            'id',
            'city',
            'district',
            'province',
            'village',
            'postalCode',
            'street'
        ]);
        try {
            $result = $this->branchService->updateBranchService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }

    /**
     * get default branchs.
     *
     * @return Branch
     * @param Request $request
     */
    public function getDefaultList(Request $request)
    {
        try {
            $result = $this->branchService->getDefaultBranchService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
