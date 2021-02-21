<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Exception;
use DB;
use Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// use App\Models\User;
use App\Utilities\ProxyRequest;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\MailService;
use App\Services\RoleService;

use App\Http\Controllers\BaseController;

class GoogleController extends BaseController
{
    protected $proxy;
    protected $authService;
    protected $userService;
    protected $mailService;
    protected $roleService;


    public function __construct(
        ProxyRequest $proxy,
        AuthService $authService,
        UserService $userService,
        MailService $mailService,
        RoleService $roleService
    )
    {
        $this->proxy = $proxy;
        $this->authService = $authService;
        $this->userService = $userService;
        $this->mailService = $mailService;
        $this->roleService = $roleService;
    }

    public function loginGoogle(Request $request)
    {
        $data = $request->only([
            'tokenId',
        ]);
        DB::beginTransaction();
        // Verify google then register or get user
        try {
            $payload = $this->authService->verifyIdTokenService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        try {
            $user = $this->userService->getByEmailService($payload['email']);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        if ($user) {
            // validate role
            try {
                $this->roleService->validateRoleLogin($user, 'customer');
            } catch (Exception $e) {
                return $this->sendError($e->getMessage());
            }

            $password = $payload['sub'].env('HASH_PASSWORD');

            if(Auth::attempt(['email' => $payload['email'], 'password' => $password])){
                DB::beginTransaction();
                try {
                    $response = $this->authService->getAccessToken(strtolower($payload['email']), $password);
                } catch (Exception $e) {
                    DB::rollback();
                    return $this->sendError($e->getMessage());
                }
                DB::commit();

                return $this->sendResponse('Berhasil login', $response);
            } else {
                return $this->sendError('Gagal login google');
            }
            DB::commit();
        }
        return $this->sendResponse('Google login gagal');
    }

    public function registerGoogle(Request $request)
    {
        $data = $request->only([
            'tokenId',
        ]);
        DB::beginTransaction();
        // Verify google then register or get user
        try {
            $payload = $this->authService->verifyIdTokenService($data);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        $password = $payload['sub'].env('HASH_PASSWORD');
        $payload = [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $password,
            'password_confirmation' => $password,
            'role_id' => 1,
            'username' => Carbon::now('Asia/Jakarta')->timestamp,
            'google_id' => $payload['sub']
        ];

        // save user
        try {
            $user = $this->userService->save($payload);
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

        // get access token
        try {
            $response = $this->authService->getAccessToken(strtolower($user->email), $password);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        DB::commit();
        return $this->sendResponse('Pengguna berhasil register', $response);
    }
}
