<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->header('Authorization');
//        $token = auth()->user()->getRememberToken();
        $token = 'Bearer eyJpdiI6InJUdmhibVF2RXRFaE1jZXQ2OFYrYlE9PSIsInZhbHVlIjoiVmtzb05UaTAxN3Nnbncybk9WNjc4S0Y5NDdvcnd0blAvQ2VDL0NJOStyN0xYQnExMFR1amRQYzJtbnpxSGhwUWFPbmRjOW9wdnRPeGNudGNPUUJJU0hnbHdINHd1QnhGZ2R5VS95dU9hRWpBMzdYd3QzTEp0KzFxQlc1QTdZdGsiLCJtYWMiOiJhOTdiMDFlMWM2YjA5MzAxNWFiYjgzY2FjYTBlNDBiZjU4YjVkZWZkMWQxZWFhZTg2NjVhMWY2MzFmMzVhZjIyIiwidGFnIjoiIn0%3D';
        if (empty($auth) || $auth !== $token) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }
        return $next($request);
    }
}
