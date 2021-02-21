<?php

namespace App\Repositories;

// MODEL
use App\Models\User;
use App\Models\VerifyUser;

// OTHER
use App\Utilities\ProxyRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use InvalidArgumentException;

// VENDOR
use Carbon\Carbon;
use Google_Client;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
class AuthRepository
{
    protected $user;
    protected $proxy;
    protected $verifyUser;

    public function __construct(User $user, VerifyUser $verifyUser, ProxyRequest $proxy)
    {
        $this->user = $user;
        $this->proxy = $proxy;
        $this->verifyUser = $verifyUser;
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

    /**
     * Create Verify Email Token
     *
     * @param String $userId
     * @return VerifyUser
     */
    public function createVerifyUser($userId)
    {
        $verify = new $this->verifyUser;
        $verify->user_id = $userId;
        $verify->token = sha1(time());
        $verify->save();
        return $verify->fresh();
    }

    /**
     * Revoke token
     *
     */
    public function revoke($tokenId)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        // Revoke an access token...
        $tokenRepository->revokeAccessToken($tokenId);

        // Revoke all of the token's refresh tokens...
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
    }

    /**
     * verify token google repo
     *
     * @param array $data
     */
    public function verifyTokenGoogleRepo($data = [])
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($data['tokenId']);
        if ($payload) {
            return $payload;
        }
        throw new InvalidArgumentException('Verifikasi google tidak berhasil');
    }
}
