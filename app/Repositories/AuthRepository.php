<?php

namespace App\Repositories;

use App\Models\User;
use App\Utilities\ProxyRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use DB;

class AuthRepository
{
    protected $user;
    protected $proxy;

    public function __construct(User $user, ProxyRequest $proxy)
    {
        $this->user = $user;
        $this->proxy = $proxy;
    }

    /**
     * get access token user login
     *
     * @param String $email
     * @param String $pass
     * @return object
     */
    public function getAccessToken($email, $pass)
    {
        // abort_unless($user, 404, 'This combination does not exists');
        // abort_unless(
        //     \Hash::check($request->password, $user->password),
        //     403,
        //     'This combination does not exists'
        // );
        $resp = $this->proxy->grantPasswordToken($email, $pass);
        $success = [
            'token' => $resp->access_token,
            'refreshToken' => $resp->refresh_token,
            'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
        ];
        return $success;
    }

    /**
     * Refresh token
     *
     * @param String $refreshToken
     * @return mixed
     */
    public function refreshToken($refreshToken)
    {
        $resp = $this->proxy->refreshAccessToken($refreshToken);
        $success = [
            'token' => $resp->access_token,
            'refreshToken' => $resp->refresh_token,
            'expiresIn' => Carbon::now()->addSecond($resp->expires_in)->toDateTimeString(),
        ];
        return $success;
    }
}
