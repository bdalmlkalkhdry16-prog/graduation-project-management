<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Department;
use App\Models\Program;
use Illuminate\View\View;

/**
 * Phase 4 — Public Portal.
 *
 * صفحات عامة بلا تسجيل دخول (زائر): معلومات الكلية، الأقسام،
 * التخصصات/البرامج، وتوجيه للقبول عبر بوابة التنسيق الرسمية الخارجية.
 * لا Migrations ولا Models جديدة — يقرأ فقط من الهيكل الأكاديمي
 * الموجود (College/Department/Specialization/Program من Phase 3).
 *
 * لا يستخدم Roles & Permissions لأن الزائر بلا تسجيل دخول أصلاً؛
 * لا يوجد شيء لتقييد وصوله إليه.
 *
 * لا علاقة له بـ routes/web.php القديم أو welcome.blade.php — مسار
 * مستقل تمامًا (/portal) حفاظًا على عدم لمس صفحة النظام القديم الرئيسية.
 */
class PublicPortalController extends Controller
{
    /**
     * الصفحة الرئيسية للبوابة: نظرة عامة على الكلية + الأقسام النشطة.
     */
    public function index(): View
    {
        $college = College::active()->first();

        $departments = Department::active()
            ->withCount(['specializations' => fn ($q) => $q->active()])
            ->orderBy('name_ar')
            ->get();

        return view('portal.home', compact('college', 'departments'));
    }

    /**
     * قائمة كل الأقسام النشطة.
     */
    public function departments(): View
    {
        $departments = Department::active()
            ->with(['specializations' => fn ($q) => $q->active()])
            ->orderBy('name_ar')
            ->get();

        return view('portal.departments.index', compact('departments'));
    }

    /**
     * تفاصيل قسم واحد: تخصصاته والبرامج المتاحة تحت كل تخصص.
     */
    public function departmentShow(Department $department): View
    {
        abort_unless($department->is_active, 404);

        $department->load(['specializations' => function ($q) {
            $q->active()->with('programs');
        }]);

        return view('portal.departments.show', compact('department'));
    }

    /**
     * تفاصيل برنامج واحد: مستوياته وإجمالي ساعاته.
     */
    public function programShow(Program $program): View
    {
        $program->load(['specialization.department', 'levels' => function ($q) {
            $q->orderBy('level_number');
        }]);

        return view('portal.programs.show', compact('program'));
    }

    /**
     * صفحة القبول: توجيه لبوابة التنسيق الرسمية الخارجية (لا قبول
     * داخلي يُبنى في هذا النظام).
     */
    public function admission(): View
    {
        $admissionUrl = 'https://tanseek.net/crgate.php?cg=12';

        return view('portal.admission', compact('admissionUrl'));
    }
}