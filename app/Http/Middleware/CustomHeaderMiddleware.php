<?php

namespace App\Http\Middleware;

use Closure;

class CustomHeaderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $headerName = 'X-Name', $headerValue = 'API')
    {
        $response = $next($request); // controller 處理完成後的回應資料
        $response->headers->set($headerName, $headerValue);
        return $response;
    }
}
