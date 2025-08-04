<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get language from request parameter
        $locale = $request->get('lang');
        
        // If no language specified, try to get from session
        if (!$locale) {
            $locale = session('locale', config('app.locale'));
        }
        
        // Set locale if valid
        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        }
        
        return $next($request);
    }
} 