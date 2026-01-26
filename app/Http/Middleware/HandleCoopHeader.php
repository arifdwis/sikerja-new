<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCoopHeader
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
        $response = $next($request);

        // Fix for Google Login Popup blocked by Cross-Origin-Opener-Policy
        // 'same-origin-allow-popups' allows the popup to communicate with the opener.
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        return $response;
    }
}
