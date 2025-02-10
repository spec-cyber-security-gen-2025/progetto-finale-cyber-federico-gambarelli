<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BlockIps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    private $maxAttempts = 2;
    private $decayMinutes = 1;


    public function handle(Request $request, Closure $next): Response
    {
        $cacheKey = $request->ip();

        if(Cache::has($cacheKey . '_block')){
            // return redirect()->route('homepage')->with('error', "Too many requests. Please try again in $this->decayMinutes minutes.");
            return response()->json(['error' => 'Too many requests. Please try again in ' . $this->decayMinutes . ' minutes.'], 429);
        }

        if (Cache::has($cacheKey)) {
            $attempts = Cache::increment($cacheKey);
            if ($attempts > $this->maxAttempts) {

                Cache::put($cacheKey . '_block', true, $this->decayMinutes * 60);
                Log::warning("IP  $cacheKey has been blocked for $this->decayMinutes minute(s) due to too many attempts.");
                // return redirect()->route('homepage')->with('error', "Too many requests. Please try again in $this->decayMinutes minutes.");
                return response()->json(['error' => "IP  $cacheKey has been blocked for $this->decayMinutes minute(s) due to too many attempts."], 429);
            }
        } else {
            Cache::put($cacheKey, 1, $this->decayMinutes * 60);
        }

        return $next($request);
    }
}
