<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SchoolMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
      if (!auth('school')->check()) {
            return redirect()->route('school.login');
        }

        $school = auth('school')->user();
        if (!$school->isAccountActive()) {
            Auth::guard('school')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('school.login')
                ->with('error_message', 'This school account has been deactivated. Please contact support.');
        }

        return $next($request);
    }
}
