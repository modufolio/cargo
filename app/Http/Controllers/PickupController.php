<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\AddressService;
use App\Services\PickupService;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

class PickupController extends BaseController
{
    protected $addressService;
    protected $pickupService;

    public function __construct(AddressService $addressService, PickupService $pickupService)
    {
        $this->addressService = $addressService;
        $this->pickupService = $pickupService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
            'fleetId',
            'promoId',
            'name',
            'phone',
            'senderId',
            'receiverId',
            'debtorId',
            'notes',
            'items',
            'origin',
            'destination_city',
            'destination_district',
            'picktime',
        ]);
        DB::beginTransaction();
        try {
            $result = $this->pickupService->save($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse(null, $result);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function paginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'name',
            'city',
            'district',
            'village',
            'picktime',
            'sort'
        ]);
        try {
            $result = $this->pickupService->getAllPaginate($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
    }
}
