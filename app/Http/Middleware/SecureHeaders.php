<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];
    public function handle(Request $request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Strict-Transport-Security', 'max-age=16070400; includeSubDomains');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        return $response;
    }
    protected function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header) {
					header_remove($header);
				}
    }
}
