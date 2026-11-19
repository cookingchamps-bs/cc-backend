<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('X-CLIENT');
        $expected = (string) config('services.api_client_key');

        if ($header === null || $header === '') {
            return Helper::responseFunction(false, 422, 'Header Authorization is required', null, 422);
        }

        if ($expected === '' || ! hash_equals($expected, $header)) {
            return Helper::responseFunction(false, 200, 'Authorization Token is Incorrect', null, 401);
        }

        return $next($request);
    }
}
