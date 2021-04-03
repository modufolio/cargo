<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\SenderService;
use App\Http\Controllers\BaseController;
use Exception;

class SenderController extends BaseController
{
    protected $userService;
    protected $senderService;

    public function __construct(UserService $userService, SenderService $senderService)
    {
        $this->userService = $userService;
        $this->senderService = $senderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $result = $this->senderService->getByUserId($request->userId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse(null, $result);
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
            'province',
            'city',
            'district',
            'village',
            'postal_code',
            'street',
            'notes',
        ]);

        try {
            $result = $this->senderService->save($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

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
        $data = $request->only([
            'userId',
            'is_primary',
            'temporary',
            'title',
            'province',
            'city',
            'district',
            'village',
            'postal_code',
            'street',
            'notes',
        ]);

        try {
            $result = $this->senderService->update($data, $id);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $result = $this->senderService->deleteById($id, $request->userId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * get primary sender address
     */
    public function getPrimary(Request $request)
    {
        try {
            $result = $this->senderService->getPrimaryService($request->userId);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }
}
