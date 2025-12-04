<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Exception;

class CatchExceptionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        try
        {
            return $next($request);
        }
        catch(ValidationException $e)
        {
            Log::error('Validation Exception: ' . $e->getMessage());

            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ] , 400);
        }
        catch(QueryException $e)
        {
            Log::error('Database Query Exception: ' . $e->getMessage());

            return response()->json([
                'message' => 'Server Error, try again later.',
            ] , 500);
        }
        catch(Exception $e)
        {
            Log::error('General Exception: ' . $e->getMessage());

            return response()->json([
                'message' => 'Server Error, try again later.',
            ] , 500);
        }
    }
}
