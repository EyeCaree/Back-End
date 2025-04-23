<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertEmptyStringsToNull
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
        $input = $request->all();

        // Mengubah string kosong menjadi null
        array_walk_recursive($input, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });

        $request->replace($input);

        return $next($request);
    }
}
