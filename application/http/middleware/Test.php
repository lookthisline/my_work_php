<?php

namespace app\http\middleware;

class Test
{
    public function handle($request, \Closure $next)
    {
        if (strtoupper($request->method()) === "OPTIONS") {
            return clientResponse(null, 'success', true, 200, [
                'Access-Control-Allow-Methods' => $request->header('access-control-request-method', ''),
                'Access-Control-Allow-Headers' => $request->header('access-control-request-headers', '')
            ]);
        }
        return $next($request);
    }
}
