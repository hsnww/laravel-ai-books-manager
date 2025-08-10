<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\LanguageHelper;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Contracts\Routing\TerminableMiddleware|\Illuminate\Contracts\Routing\ResponseFactory)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // تحديد اللغة من عدة مصادر
        $locale = null;
        
        // 1. من query parameter
        if ($request->has('lang')) {
            $locale = $request->get('lang');
        }
        
        // 2. من session
        if (!$locale && Session::has('locale')) {
            $locale = Session::get('locale');
        }
        
        // 3. من Accept-Language header
        if (!$locale) {
            $locale = $request->getPreferredLanguage(['ar', 'en']);
        }
        
        // 4. اللغة الافتراضية
        if (!$locale || !LanguageHelper::isValidLanguage($locale)) {
            $locale = 'en';
        }
        
        // تعيين اللغة
        LanguageHelper::setLanguage($locale);
        
        return $next($request);
    }
} 