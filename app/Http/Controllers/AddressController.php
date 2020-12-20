<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\AddressService;
use App\Http\Controllers\BaseController;
use Exception;

class AddressController extends BaseController
{
    protected $userService;
    protected $addressService;

    public function __construct(UserService $userService, AddressService $addressService)
    {
        $this->userService = $userService;
        $this->addressService = $addressService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $result = $this->addressService->getAddressUser($request->userId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'userId',
            'is_primary',
            'temporary',
            'title',
            'receiptor',
            'phone',
            'province',
            'city',
            'district',
            'village',
            'postal_code',
            'street',
            'notes',
        ]);

        try {
            $result = $this->addressService->saveAddressData($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $addressId
     * @return \Illuminate\Http\Response
     */
    public function destroy($addressId)
    {
        try {
            $result = $this->addressService->deleteById($addressId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($result);
    }
}
