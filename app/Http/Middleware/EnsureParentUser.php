<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use App\Models\Kid;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof Kid || ($user->role ?? null) === 'kid') {
            return Helper::responseFunction(false, 403, 'This action requires a parent account.', null, 403);
        }

        if (! $user instanceof User) {
            return Helper::responseFunction(false, 403, 'Not authorized.', null, 403);
        }

        return $next($request);
    }
}