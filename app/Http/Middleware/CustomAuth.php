<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PeterPetrus\Auth\PassportToken;
use Illuminate\Support\Facades\DB;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $token = PassportToken::dirtyDecode($token);
        if ($token['valid']) {
            $tokenExist = PassportToken::existsValidToken($token['token_id'], $token['user_id']);
            if ($tokenExist) {
                if (($user = Auth::user()) !== null) {
                    $request->route()->setParameter('userId', $user->id);
                    return $next($request);
                }
                return $this->errorResponse(['code' => 4001], 'unathorized', 401);
            }
            return $this->errorResponse(['code' => 4002], 'unathorized', 401);
        }
        return $this->errorResponse(['code' => 4003], 'unathorized', 401);
    }

    protected function errorResponse($error = null, $message = null, $code = 403)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error' => $error
        ];

        return response()->json($response, $code);
    }
}
