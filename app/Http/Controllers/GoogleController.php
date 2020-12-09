<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Auth;
use Exception;
use DB;
use Log;

use App\Models\User;
use App\Utilities\ProxyRequest;
use App\Services\AuthService;
use App\Services\UserService;

use App\Http\Controllers\BaseController;

class GoogleController extends BaseController
{
    protected $proxy;
    protected $authService;
    protected $userService;

    public function __construct(ProxyRequest $proxy, AuthService $authService, UserService $userService)
    {
        $this->proxy = $proxy;
        $this->authService = $authService;
        $this->userService = $userService;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        DB::beginTransaction();
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();
            if ($finduser) {
                // Auth::login($finduser);
                $response = $this->authService->getAccessToken(strtolower($finduser->email), $finduser->password);
            } else {
                $pass = bcrypt($user->id.strtolower($user->email));

                $newUser = [
                    'name'                  => $user->name,
                    'email'                 => strtolower($user->email),
                    'google_id'             => $user->id,
                    'role_id'               => 1, // role customer
                    'username'              => $user->id,
                    'password'              => $pass,
                    'password_confirmation' => $pass
                ];

                // save user
                try {
                    $userData = $this->userService->save($newUser);
                } catch (Exception $e) {
                    DB::rollback();
                    return $this->sendError($e->getMessage());
                }


                // get access token
                try {
                    $response = $this->authService->getAccessToken(strtolower($userData->email), $pass);
                } catch (Exception $e) {
                    DB::rollback();
                    return $this->sendError($e->getMessage());
                }

                // Auth::login($newUser);
                // return redirect('/dashboard');
            }

        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse('Pengguna berhasil login dengan google', $response);
    }

}
