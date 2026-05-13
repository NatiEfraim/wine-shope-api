<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Request: ' . $request->method() . ' ' . $request->fullUrl() . ' Origin: ' . ($request->header('Origin') ?? 'none'));
        $response = $next($request);
        Log::info('Response status: ' . $response->getStatusCode() . ' for ' . $request->method() . ' ' . $request->fullUrl());
        return $response;
    }
}
