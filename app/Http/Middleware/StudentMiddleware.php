<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !(auth()->user()->isStudent() || auth()->user()->isAdmin())) {
            abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة');
        }

        return $next($request);
    }
}
