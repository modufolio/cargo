<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Controllers\BaseController;
use App\Services\LoginService;
use App\Utilities\ProxyRequest;
use Carbon\Carbon;
use DB;

class AuthController extends BaseController
{
    protected $proxy;

    public function __construct(ProxyRequest $proxy)
    {
        $this->proxy = $proxy;
    }

    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            DB::beginTransaction();
            $user = Auth::user();
            abort_unless($user, 404, 'This combination does not exists.');
            abort_unless(
                \Hash::check($request->password, $user->password),
                403,
                'This combination does not exists.'
            );

            $resp = $this->proxy->grantPasswordToken($request->email, $request->password);
            $success = [
                'token' => $resp->access_token,
                'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
            ];
            DB::commit();
            return $this->sendResponse($success, 'User login successfully.');
        } else {

            return $this->sendError('Unauthorised', ['error'=>'Unauthorised']);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'role_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        DB::beginTransaction();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id
        ]);

        $resp = $this->proxy->grantPasswordToken(
            $user->email,
            $request->password
        );

        $success = [
            'token' => $resp->access_token,
            'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
        ];
        DB::commit();
        return $this->sendResponse($success, 'User register successfully');
    }

    public function refreshToken()
    {
        $resp = $this->proxy->refreshAccessToken();

        $success = [
            'token' => $resp->access_token,
            'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
        ];

        return $this->sendResponse($success, 'Token has been refreshed');
    }

    public function logout(Request $request)
    {
        $token = request()->user()->token();
        $token->delete();

        // remove the httponly cookie
        cookie()->queue(cookie()->forget('refresh_token'));

        return $this->sendResponse(null, 'Successfully logged out');
    }
}
