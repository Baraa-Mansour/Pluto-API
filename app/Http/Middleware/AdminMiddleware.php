<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;


class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if($request->user() && $request->user()->role === User::ROLE_ADMIN)
        {
            return $next($request);
        }
        
        return response()->json([
            'message' => 'Forbidden'
        ], 403);
    }
}
