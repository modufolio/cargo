<?php

namespace App\Http\Controllers;

// MODEL
use App\Models\Menu;

// SERVICE
use App\Services\RoleService;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class MenuController extends BaseController
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $menus = Menu::with(['submenus' => function($q) {
            $q->orderBy('id', 'ASC');
          }])->orderBy('id', 'ASC')->get();
        return response()->json($menus);
    }

    /**
     * get accessible menu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAccessibleMenu(Request $request)
    {
        $data = $request->only([
            'userId'
        ]);
        try {
            $result = $this->roleService->getMenuRoleService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
