<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate للوصول إلى لوحة التحكم
        Gate::define('access-admin', function ($user) {
            return $user->hasRole('super_admin');
        });

        // Gate للوصول إلى إدارة الملفات
        Gate::define('manage-files', function ($user) {
            return $user->hasRole(['super_admin', 'admin']);
        });

        // Gate للوصول إلى معالجة الذكاء الاصطناعي
        Gate::define('ai-processing', function ($user) {
            return $user->hasRole(['super_admin', 'admin']);
        });
    }
} 