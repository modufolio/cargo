<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\MailService;
use App\Utilities\ProxyRequest;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use Validator;
use DB;
use Exception;

class AuthController extends BaseController
{
    protected $proxy;
    protected $authService;
    protected $userService;
    protected $mailService;

    public function __construct(ProxyRequest $proxy, AuthService $authService, UserService $userService, MailService $mailService)
    {
        $this->proxy = $proxy;
        $this->authService = $authService;
        $this->userService = $userService;
        $this->mailService = $mailService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'bail|required',
            'password' => 'bail|required',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->first('userId')) {
                return $this->sendError($validator->errors()->first('userId'), 4001);
            }
            if ($validator->errors()->first('password')) {
                return $this->sendError($validator->errors()->first('password'), 4002);
            }
        }

        $useEmail = filter_var(strtolower($request->userId), FILTER_VALIDATE_EMAIL);

        if (!$useEmail) {
            $user = User::where('username', strtolower($request->userId))->first();
            if (!$user) {
                return $this->sendError('Username tersebut tidak cocok dengan data kami', 4003);
            }
        } else {
            $user = User::where('email', strtolower($request->userId))->first();
            if (!$user) {
                return $this->sendError('Email tersebut tidak cocok dengan data kami', 4004);
            }
        }

        $request->merge(['email' => $user->email]);

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            DB::beginTransaction();
            // $user = Auth::user();

            try {
                $response = $this->authService->getAccessToken(strtolower($request->email), $request->password);
            } catch (Exception $e) {
                DB::rollback();
                return $this->sendError($e->getMessage());
            }
            DB::commit();

            return $this->sendResponse('Berhasil login', $response);
        } else {
            return $this->sendError('Password tersebut tidak cocok dengan data kami', 4005);
        }
    }

    public function register(Request $request)
    {
        DB::beginTransaction();

        // save user
        try {
            $user = $this->userService->save($request->all());
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
            $response = $this->authService->getAccessToken(strtolower($user->email), $request->password);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        DB::commit();
        return $this->sendResponse('Pengguna berhasil register', $response);
    }

    public function refreshToken(Request $request)
    {
        DB::beginTransaction();
        try {
            $response = $this->authService->refreshToken($request->refreshToken);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        DB::commit();
        return $this->sendResponse('Token telah di perbarui', $response);
    }

    public function logout(Request $request)
    {
        $token = request()->user()->token();
        $token->delete();

        // remove the httponly cookie
        // cookie()->queue(cookie()->forget('refresh_token'));

        return $this->sendResponse('Successfully logged out');
    }

    public function checkLogin(Request $request)
    {
        if (Auth::guard('api')->check()) {
            // Here you have access to $request->user() method that
            // contains the model of the currently authenticated user.
            //
            // Note that this method should only work if you call it
            // after an Auth::check(), because the user is set in the
            // request object by the auth component after a successful
            // authentication check/retrival
            return response()->json($request->user());
        }

        // alternative method
        if (($user = Auth::user()) !== null) {
        //     // Here you have your authenticated user model
            return response()->json($user);
        }

        return $this->sendError('Unauthenticated user', null);

        // return response('Unauthenticated user');
    }
}
