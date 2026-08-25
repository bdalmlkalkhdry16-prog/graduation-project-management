<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Phase 1 — Roles & Permissions (نظام جديد، إضافي فقط).
        // من يحمل دور "admin" الجديد (عبر user_roles) يحصل على تجاوز كامل،
        // بما يماثل تمامًا سلوك isAdmin() الحالي، دون أي تعارض معه.
        // لا يؤثر هذا على أي Middleware أو Route قديم، لأن لا شيء قديم
        // يستخدم Gate حاليًا.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
