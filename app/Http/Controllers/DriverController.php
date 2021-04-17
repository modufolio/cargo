<?php

namespace App\Http\Controllers;

// OTHER
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Exception;
use DB;

// SERVICE
use App\Services\DriverService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\AuthService;
use App\Services\AddressService;

class DriverController extends BaseController
{
    protected $driverService;
    protected $userService;
    protected $mailService;
    protected $authService;
    protected $addressService;

    public function __construct(
        AuthService $authService,
        DriverService $driverService,
        UserService $userService,
        MailService $mailService,
        AddressService $addressService
    )
    {
        $this->driverService = $driverService;
        $this->userService = $userService;
        $this->mailService = $mailService;
        $this->authService = $authService;
        $this->addressService = $addressService;
    }

    /**
     * Searching driver by vehicle id.
     *
     * @param Request $request
     * @return Driver
     */
    public function search(Request $request)
    {
        $data = $request->only([
            'value',
            'type'
        ]);

        try {
            $result = $this->driverService->getDriverService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * Paginate driver
     *
     * @param Request $request
     * @return Driver
     */
    public function paginate(Request $request)
    {
        $data = $request->only([
            'perPage',
            'page',
            'id',
            'email',
            'name',
            'active',
            'status',
            'type',
            'branch',
            'phone',
            'sort'
        ]);

        try {
            $result = $this->driverService->getAllPaginateService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * edit driver.
     *
     * @param Request $request
     * @return Driver
     */
    public function edit(Request $request)
    {
        $data = $request->only([
            'id',
            'name',
            'phone',
            'email',
            'active',
            'branchId',
            'type',
            'province',
            'city',
            'district',
            'village',
            'street',
            'postalCode'
        ]);
        try {
            $result = $this->driverService->editDriverService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * add driver.
     *
     * @param Request $request
     * @return Driver
     */
    public function create(Request $request)
    {
        $username = explode("@", $request->email, 2);
        $userData = [
            'password' => 'driver123',
            'password_confirmation' => 'driver123',
            'role_id' => 3,
            'username' => $username[0],
        ];
        $userData = array_merge($userData, $request->all());
        DB::beginTransaction();
        // save user
        try {
            $user = $this->userService->saveDriver($userData);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        // create verify user
        try {
            $verifyUser = $this->authService->createVerifyUser($user->id);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        // send email verification
        try {
            $this->mailService->sendEmailVerification($user, $verifyUser);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        $address = [
            'userId' => $user->id,
            'postal_code' => $request->postalCode
        ];
        $address = array_merge($request->all(), $address);
        // save address
        try {
            $this->addressService->saveAddressData($address);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        // create driver
        try {
            $this->driverService->createDriverService($request->all(), $user->id);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        // update branch user
        $data = [
            'userId' => $user->id,
            'branchId' => $request->branchId
        ];
        try {
            $this->userService->updateBranchService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        DB::commit();

        return $this->sendResponse('Success Menambahkan driver');
    }

    /**
     * disable driver.
     *
     * @param Request $request
     * @return Driver
     */
    public function disable(Request $request)
    {
        $data = $request->only([
            'driverId',
        ]);
        try {
            $result = $this->driverService->disableDriverService($data);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }

    /**
     * get initial default driver list.
     *
     * @param Request $request
     * @return Driver
     */
    public function getDefaultList(Request $request)
    {
        try {
            $result = $this->driverService->getDefaultDriversService();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse(null, $result);
    }
}
