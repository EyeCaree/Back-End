<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Response;

class PreventRequestsDuringMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah aplikasi dalam mode pemeliharaan
        if (App::isDownForMaintenance()) {
            return response('Service Unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
