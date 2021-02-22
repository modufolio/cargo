<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverPanel
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
        $user = Auth::user();
        if ($user !== null) {
            $role = $user->role()->first();
            if ($role->slug !== 'driver') {
                return $this->errorResponse(['code' => 4001], 'unathorized', 401);
            }
            $response = $next($request);
            return $response;
        }
        return $this->errorResponse(['code' => 4002], 'unathorized', 401);

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
