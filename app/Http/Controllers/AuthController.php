<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\LoginService;
use App\Utilities\ProxyRequest;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use Validator;
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
                return $this->sendError('Identitas tersebut tidak cocok dengan data kami', 4003);
            }
			$request->merge(["email" => $user->email]);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            DB::beginTransaction();
            $user = Auth::user();
            // abort_unless($user, 404, 'This combination does not exists');
            // abort_unless(
            //     \Hash::check($request->password, $user->password),
            //     403,
            //     'This combination does not exists'
            // );

            $resp = $this->proxy->grantPasswordToken($request->email, $request->password);
            $success = [
                'token' => $resp->access_token,
                'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
            ];
            DB::commit();
            return $this->sendResponse('Berhasil login', $success);
        } else {
            return $this->sendError('Identitas tersebut tidak cocok dengan data kami', 4003);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required|confirmed',
            'role_id' => 'bail|required',
            'username' => 'bail|required|unique:users'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        DB::beginTransaction();
        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => bcrypt($request->password),
            'username' => bcrypt($request->username),
            'role_id' => $request->role_id
        ]);

        $resp = $this->proxy->grantPasswordToken(
            strtolower($request->email),
            $request->password
        );

        $success = [
            'token' => $resp->access_token,
            'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
        ];
        DB::commit();
        return $this->sendResponse('Pengguna berhasil register', $success);
    }

    public function refreshToken()
    {
        $resp = $this->proxy->refreshAccessToken();

        $success = [
            'token' => $resp->access_token,
            'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
        ];

        return $this->sendResponse('Token telah di perbarui', $success);
    }

    public function logout(Request $request)
    {
        $token = request()->user()->token();
        $token->delete();

        // remove the httponly cookie
        cookie()->queue(cookie()->forget('refresh_token'));

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
            // Here you have your authenticated user model
            return response()->json($user);
        }

        return response('Unauthenticated user');
    }
}
