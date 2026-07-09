<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectFileController;
use App\Http\Controllers\Project\ProjectMemberController;
use App\Http\Controllers\Evaluation\EvaluationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DevelopmentRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// الصفحات العامة
// =========================================================================
Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/help', function () {
    return view('help');
})->name('help')->middleware('auth');

// =========================================================================
// المصادقة (Auth)
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =========================================================================
// لوحة التحكم (Dashboard)
// =========================================================================
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

// =========================================================================
// الملف الشخصي
// =========================================================================
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
});

// =========================================================================
// المشاريع (Projects)
// =========================================================================

// مسار المشاريع قيد المراجعة (قبل resource)
Route::middleware('auth')->get('/projects/pending-review', [ProjectController::class, 'pendingReview'])->name('projects.pending_review');
Route::middleware('auth')->get('/projects/archive', [ProjectController::class, 'archive'])->name('projects.archive');
Route::post('{project}/review', [ProjectController::class, 'review'])->name('projects.review');
// مسارات المناقشات (Defense)
Route::middleware('auth')->put('/projects/{project}/set-defense', [ProjectController::class, 'setDefenseDate'])->name('projects.set_defense');
Route::middleware('auth')->get('/defense-schedule', [ProjectController::class, 'defenseSchedule'])->name('defense.schedule');

// مجموعة المسارات المخصصة (تأتي قبل Route::resource)
Route::middleware('auth')->prefix('projects')->name('projects.')->group(function () {
    Route::get('idea/{idea}', [ProjectController::class, 'showIdea'])->name('idea.show');

    // مسارات الأفكار – ثابتة
    Route::get('create-idea', [ProjectController::class, 'createIdea'])->name('create_idea');
    Route::post('submit-idea', [ProjectController::class, 'submitIdea'])->name('submit_idea');
    Route::post('{project}/review-idea', [ProjectController::class, 'reviewIdea'])->name('review_idea');
    Route::post('idea/{idea}/review', [ProjectController::class, 'reviewIdea'])->name('idea.review');

    // مسارات الملفات والأعضاء – تعتمد على {project}
    Route::prefix('{project}/files')->name('files.')->group(function () {
        Route::get('/', [ProjectFileController::class, 'index'])->name('index');
        Route::post('/', [ProjectFileController::class, 'store'])->name('store');
    });
    Route::prefix('files')->name('files.')->group(function () {
        Route::delete('{file}', [ProjectFileController::class, 'destroy'])->name('destroy');
        Route::get('{file}/download', [ProjectFileController::class, 'download'])->name('download');
        Route::post('{file}/approve', [ProjectFileController::class, 'approve'])->name('approve');
    });

    Route::prefix('{project}/members')->name('members.')->group(function () {
        Route::get('/', [ProjectMemberController::class, 'index'])->name('index');
        Route::get('/create', [ProjectMemberController::class, 'create'])->name('create');
        Route::post('/', [ProjectMemberController::class, 'store'])->name('store');
        Route::put('{member}/role', [ProjectMemberController::class, 'updateRole'])->name('update-role');
        Route::delete('{member}', [ProjectMemberController::class, 'destroy'])->name('destroy');
        Route::get('/invite', [ProjectMemberController::class, 'showInviteForm'])->name('invite');
        Route::post('/invite', [ProjectMemberController::class, 'sendInvite'])->name('send-invite');
    });

    // تقديم المشروع للمراجعة ومراجعة المشروع
    Route::post('{project}/submit', [ProjectController::class, 'submit'])->name('submit');
    Route::post('{project}/review', [ProjectController::class, 'review'])->name('review');
});

// CRUD الأساسي – يأتي بعد المسارات المخصصة
Route::middleware('auth')->resource('projects', ProjectController::class);

// قبول الدعوة (خارج مجموعة projects)
Route::get('projects/invite/{code}', [ProjectMemberController::class, 'acceptInvite'])
    ->name('projects.invite.accept')
    ->middleware('auth');

// =========================================================================
// التقييم (Evaluations)
// =========================================================================

// مسارات سجل التقييمات (للمشرف والطالب والمدير) – خارج صلاحيات المشرف
Route::middleware('auth')->get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
Route::middleware('auth')->get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');

// مسارات إنشاء وتعديل التقييم (للمشرف فقط)
Route::middleware(['auth', 'supervisor'])->prefix('evaluations')->name('evaluations.')->group(function () {
    Route::get('project/{project}', [EvaluationController::class, 'create'])->name('create');
    Route::post('project/{project}', [EvaluationController::class, 'store'])->name('store');
    Route::get('{evaluation}/edit', [EvaluationController::class, 'edit'])->name('edit');
    Route::put('{evaluation}', [EvaluationController::class, 'update'])->name('update');
    Route::post('{evaluation}/submit', [EvaluationController::class, 'submit'])->name('submit');
});

// =========================================================================
// التعليقات
// =========================================================================
Route::middleware('auth')->prefix('comments')->name('comments.')->group(function () {
    Route::post('project/{project}', [CommentController::class, 'store'])->name('store');
    Route::put('{comment}', [CommentController::class, 'update'])->name('update');
    Route::delete('{comment}', [CommentController::class, 'destroy'])->name('destroy');
});

// =========================================================================
// الإشعارات
// =========================================================================
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('{notification}', [NotificationController::class, 'show'])->name('show');
    Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
    Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::get('unread/count', [NotificationController::class, 'unreadCount'])->name('unread-count');
});

// =========================================================================
// طلبات تطوير المشاريع
// =========================================================================
Route::middleware('auth')->prefix('development-requests')->name('development-requests.')->group(function () {
    Route::get('/', [DevelopmentRequestController::class, 'index'])->name('index');
    Route::get('project/{project}/create', [DevelopmentRequestController::class, 'create'])->name('create');
    Route::post('project/{project}', [DevelopmentRequestController::class, 'store'])->name('store');
    Route::get('{developmentRequest}', [DevelopmentRequestController::class, 'show'])->name('show');
    Route::get('{developmentRequest}/edit', [DevelopmentRequestController::class, 'edit'])->name('edit');
    Route::put('{developmentRequest}', [DevelopmentRequestController::class, 'update'])->name('update');
    Route::post('{developmentRequest}/approve', [DevelopmentRequestController::class, 'approve'])->name('approve');
    Route::post('{developmentRequest}/reject', [DevelopmentRequestController::class, 'reject'])->name('reject');
});

// =========================================================================
// طلبات تغيير المشرف (Supervisor Change Requests)
// =========================================================================
Route::middleware('auth')->prefix('supervisor-requests')->name('supervisor-requests.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\SupervisorChangeRequestController::class, 'index'])->name('index');
    Route::get('project/{project}/create', [\App\Http\Controllers\Admin\SupervisorChangeRequestController::class, 'create'])->name('create');
    Route::post('project/{project}', [\App\Http\Controllers\Admin\SupervisorChangeRequestController::class, 'store'])->name('store');
    Route::get('{supervisorChangeRequest}', [\App\Http\Controllers\Admin\SupervisorChangeRequestController::class, 'show'])->name('show');
    Route::post('{supervisorChangeRequest}/approve', [\App\Http\Controllers\Admin\SupervisorChangeRequestController::class, 'approve'])->name('approve');
    Route::post('{supervisorChangeRequest}/reject', [\App\Http\Controllers\Admin\SupervisorChangeRequestController::class, 'reject'])->name('reject');
});

// =========================================================================
// مسارات المدير (Admin)
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');

    Route::resource('colleges', \App\Http\Controllers\Admin\CollegeController::class);
    Route::post('colleges/{college}/toggle-status', [\App\Http\Controllers\Admin\CollegeController::class, 'toggleStatus'])
        ->name('colleges.toggle-status');

    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
    Route::post('departments/{department}/toggle-status', [\App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])
        ->name('departments.toggle-status');

    Route::resource('specializations', \App\Http\Controllers\Admin\SpecializationController::class);
    Route::post('specializations/{specialization}/toggle-status', [\App\Http\Controllers\Admin\SpecializationController::class, 'toggleStatus'])
        ->name('specializations.toggle-status');

    Route::resource('academic-years', \App\Http\Controllers\Admin\AcademicYearController::class)->except(['show']);
    Route::post('academic-years/{academicYear}/set-active', [\App\Http\Controllers\Admin\AcademicYearController::class, 'setActive'])
        ->name('academic-years.set-active');

    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('reports.generate');
    Route::get('reports/export/{type}', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
});

// =========================================================================
// مسارات API (للـ AJAX)
// =========================================================================
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('colleges/{college}/departments', function ($collegeId) {
        return response()->json(
            \App\Models\Department::where('college_id', $collegeId)
                ->where('is_active', true)
                ->get(['id', 'name_ar', 'name_en'])
        );
    })->name('colleges.departments');

    Route::get('departments/{department}/specializations', function ($departmentId) {
        return response()->json(
            \App\Models\Specialization::where('department_id', $departmentId)
                ->where('is_active', true)
                ->get(['id', 'name_ar', 'name_en'])
        );
    })->name('departments.specializations');

    Route::get('specializations/{specialization}/students', function ($specializationId) {
        return response()->json(
            \App\Models\User::where('role', 'student')
                ->where('specialization_id', $specializationId)
                ->where('is_active', true)
                ->get(['id', 'name', 'student_id', 'email'])
        );
    })->name('specializations.students');

    Route::get('specializations/{specialization}/supervisors', function ($specializationId) {
        return response()->json(
            \App\Models\User::where('role', 'supervisor')
                ->where('specialization_id', $specializationId)
                ->where('is_active', true)
                ->get(['id', 'name', 'employee_id', 'email'])
        );
    })->name('specializations.supervisors');
});

// =========================================================================
// مسارات اختبار الصلاحيات
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::get('/test-admin', fn() => '✅ مدير')->middleware('admin')->name('test.admin');
    Route::get('/test-supervisor', fn() => '✅ مشرف')->middleware('supervisor')->name('test.supervisor');
    Route::get('/test-student', fn() => '✅ طالب')->middleware('student')->name('test.student');
});

// =========================================================================
// صفحة الخطأ 404
// =========================================================================
Route::fallback(function () {
    return view('errors.404');
});