<?php

namespace App\Support;

use App\Models\AcademicSetting;
use App\Models\Exam;
use App\Models\FeeSchedule;
use App\Models\StudentFee;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/** روابط الوحدات الأكاديمية حسب صلاحيات المستخدم: [route, icon, label, pattern, group]. */
class NavLinks
{
    public static function academic(User $u): array
    {
        $isAdmin = $u->isAdmin() || $u->hasRole('admin');
        $has = fn (string $p) => $isAdmin || $u->hasPermission($p);
        return array_values(array_filter([
        // الطالب
        $has('enrollments.viewOwn') ? ['student.enrollments.index', 'fa-clipboard-list', 'تسجيلاتي', 'student.enrollments.*', 'الشؤون الأكاديمية'] : null,
        $has('schedules.viewOwn') && $u->hasPermission('enrollments.viewOwn') ? ['student.schedule.index', 'fa-calendar-week', 'جدولي', 'student.schedule.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('attendance.viewOwn') ? ['student.attendance.index', 'fa-user-check', 'حضوري', 'student.attendance.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('results.viewOwn') ? ['student.results.index', 'fa-poll', 'نتائجي', 'student.results.*', 'الاختبارات والنتائج'] : null,
        $u->hasPermission('results.viewOwn') ? ['student.academic-record', 'fa-book-reader', 'سجلي الأكاديمي', 'student.academic-record', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('results.viewOwn') ? ['student.transcript', 'fa-file-alt', 'كشف الدرجات', 'student.transcript*', 'الاختبارات والنتائج'] : null,
        $u->hasPermission('fees.viewOwn') ? ['student.fees.index', 'fa-money-bill-wave', 'رسومي', 'student.fees.*', 'الإدارة المالية'] : null,
        $u->hasPermission('service-requests.manageOwn') ? ['student.service-requests.index', 'fa-concierge-bell', 'الخدمات الإلكترونية', 'student.service-requests.*', 'الخدمات الإلكترونية'] : null,
        // عضو هيئة التدريس
        $u->hasPermission('schedules.viewOwn') && ! $u->hasPermission('enrollments.viewOwn') ? ['faculty.schedule.index', 'fa-calendar-week', 'جدولي التدريسي', 'faculty.schedule.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('enrollments.viewOwnSections') ? ['faculty.enrollments.index', 'fa-users', 'طلاب شعبي', 'faculty.enrollments.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('attendance.manageOwn') ? ['faculty.attendance.index', 'fa-user-check', 'حضور شعبي', 'faculty.attendance.*', 'الشؤون الأكاديمية'] : null,
        Gate::allows('viewAny', Exam::class) ? ['exams.index', 'fa-pen-alt', 'الاختبارات والدرجات', 'exams.*', 'الاختبارات والنتائج'] : null,
        // الموظفون
        $has('student-profiles.manage') ? ['staff.student-profiles.index', 'fa-id-card', 'ملفات الطلاب', 'staff.student-profiles.*', 'شؤون الطلاب'] : null,
        $has('enrollments.manage') ? ['staff.enrollments.index', 'fa-user-plus', 'التسجيل في الشعب', 'staff.enrollments.index', 'شؤون الطلاب'] : null,
        $has('enrollments.manage') ? ['staff.enrollments.students', 'fa-address-book', 'سجل الطلاب', 'staff.enrollments.students', 'شؤون الطلاب'] : null,
        $has('rooms.manage') ? ['staff.rooms.index', 'fa-door-open', 'القاعات', 'staff.rooms.*', 'الجداول الدراسية'] : null,
        $has('schedules.manage') ? ['staff.schedules.index', 'fa-calendar-alt', 'الجداول الدراسية', 'staff.schedules.*', 'الجداول الدراسية'] : null,
        $has('attendance.viewAll') ? ['staff.attendance.index', 'fa-clipboard-check', 'الحضور', 'staff.attendance.*', 'شؤون الطلاب'] : null,
        $has('service-requests.manageAll') ? ['staff.service-requests.index', 'fa-concierge-bell', 'طلبات الخدمات', 'staff.service-requests.*', 'الخدمات الإلكترونية'] : null,
        $has('results.review') ? ['results.review', 'fa-tasks', 'مراجعة النتائج', 'results.review', 'الاختبارات والنتائج'] : null,
        $has('results.publish') ? ['results.publish', 'fa-bullhorn', 'نشر النتائج', 'results.publish', 'الاختبارات والنتائج'] : null,
        $has('academic-records.viewAll') ? ['academic-stats', 'fa-chart-line', 'إحصاءات أكاديمية', 'academic-stats', 'التقارير والإحصائيات'] : null,
        Gate::allows('viewAny', StudentFee::class) ? ['accounting.fees.index', 'fa-cash-register', 'رسوم الطلاب والمدفوعات', 'accounting.fees.*', 'الإدارة المالية'] : null,
        Gate::allows('viewAny', StudentFee::class) ? ['accounting.reports.fees', 'fa-chart-pie', 'تقارير الرسوم', 'accounting.reports.*', 'الإدارة المالية'] : null,
        Gate::allows('viewAny', FeeSchedule::class) ? ['fee-schedules.index', 'fa-tags', 'أسعار الرسوم', 'fee-schedules.*', 'الإدارة المالية'] : null,
        Gate::allows('manage', AcademicSetting::class) ? ['academic-settings.edit', 'fa-sliders-h', 'الإعدادات الأكاديمية', 'academic-settings.*', 'إعدادات النظام'] : null,
        ]));
    }
}
