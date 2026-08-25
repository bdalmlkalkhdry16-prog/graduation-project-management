<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Phase 1 — Roles & Permissions.
 *
 * Middleware عام جديد لنظام الصلاحيات الجديد، بصيغة استخدام مستقبلية:
 * ->middleware('permission:students.create')
 *
 * لا يُطبَّق على أي Route حالي في هذه المرحلة — يبدأ استخدامه الفعلي
 * فقط مع أول Module جديدة (لا يوجد أي تعديل على routes/web.php الحالي).
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (! auth()->check() || ! auth()->user()->hasPermission($permission)) {
            abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة');
        }

        return $next($request);
    }
}
