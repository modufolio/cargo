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
            'sort'
        ]);
        try {
            $result = $this->branchService->getAllPaginate($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
