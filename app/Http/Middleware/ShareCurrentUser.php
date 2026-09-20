<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCurrentUser
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('currentUser', $request->user());

        return $next($request);
    }
}
