<?php

namespace Tests\Feature;

use App\Models\FeeSchedule;
use App\Models\Notification;
use App\Models\StudentServiceRequest;
use App\Services\EnrollmentService;
use App\Services\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class AcademicNotificationsTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $studentUser;
    private $profile;
    private $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->exam = $this->makeExam();
        [$this->studentUser, $this->profile] = $this->makeStudent();
        $this->setGrade($this->exam, $this->enroll($this->profile), 75);
    }

    private function titles($user): array
    {
        return Notification::where('user_id', $user->id)->pluck('title')->all();
    }

    /** @test */
    public function the_results_cycle_notifies_the_right_people_at_each_step()
    {
        $reviewer = $this->userWithRole('control');
        $outsider = $this->userWithRole('accountant');

        $this->actingAs($this->facultyUser)->post(route('exams.submit', $this->exam))->assertSessionHasNoErrors();
        $this->assertContains('نتائج بانتظار المراجعة', $this->titles($reviewer));
        $this->assertSame([], $this->titles($outsider));
        $this->assertNotContains('نتائج بانتظار المراجعة', $this->titles($this->facultyUser), 'المنفِّذ لا يُشعَر بفعله');

        $this->actingAs($reviewer)->post(route('exams.reject', $this->exam), ['reason' => 'درجات ناقصة'])->assertSessionHasNoErrors();
        $this->assertContains('رُفضت النتائج وتحتاج مراجعة', $this->titles($this->facultyUser));
        $this->assertStringContainsString('درجات ناقصة', Notification::where('user_id', $this->facultyUser->id)->where('title', 'like', 'رُفضت%')->value('message'));

        $this->actingAs($this->facultyUser)->post(route('exams.submit', $this->exam));
        $this->actingAs($reviewer)->post(route('exams.approve', $this->exam))->assertSessionHasNoErrors();
        $this->assertContains('اعتُمدت نتائجك', $this->titles($this->facultyUser));

        $this->assertSame([], $this->titles($this->studentUser), 'الطالب لا يُشعَر قبل النشر');
        $this->actingAs($reviewer)->post(route('exams.publish', $this->exam))->assertSessionHasNoErrors();

        $n = Notification::where('user_id', $this->studentUser->id)->get();
        $this->assertCount(1, $n);
        $this->assertSame('نُشرت نتائج جديدة', $n->first()->title);
        $this->assertSame(route('student.results.index'), $n->first()->link);
        $this->assertStringNotContainsString('75', $n->first()->message, 'لا تُكتب الدرجة في الإشعار');
    }

    /** @test */
    public function fee_events_notify_the_student_only()
    {
        $accountant = $this->userWithRole('accountant');
        $admin = $this->userWithRole('admin', 'admin');
        $year = $this->term->academicYear;
        FeeSchedule::create(['department_id' => $this->spec->department_id, 'academic_year_id' => $year->id, 'amount' => 100, 'currency' => 'USD']);

        $fees = app(FeeService::class);
        $fee = $fees->generateFor($this->profile, $year, $accountant);
        $payment = $fees->recordPayment($fee, 40, now()->toDateString(), null, $accountant);
        $discount = $fees->addDiscount($fee->fresh(), 'amount', 10, 'منحة دراسية', $admin);
        $fees->revokeDiscount($discount, 'أُلغيت المنحة', $admin);
        $fees->voidPayment($payment, 'تحويل لم يصل', $admin);
        $fees->setResultsRelease($fee->fresh(), true, 'استثناء إداري', $admin);

        $this->assertEqualsCanonicalizing(
            ['رسوم دراسية جديدة', 'تم استلام دفعة', 'خصم على رسومك', 'أُلغي خصم', 'أُلغيت دفعة', 'رُفع حجب نتائجك'],
            $this->titles($this->studentUser)
        );
        $this->assertSame([], $this->titles($accountant));
        $this->assertSame([], $this->titles($admin));
    }

    /** @test */
    public function enrollment_events_notify_the_student_and_only_on_real_changes()
    {
        [$u2, $p2] = $this->makeStudent();
        $svc = app(EnrollmentService::class);
        $e = $svc->enroll($p2, $this->section);
        $this->assertContains('تم تسجيلك في مقرر', $this->titles($u2));

        $svc->changeStatus($e, 'active'); // لا تغيير فعلي
        $this->assertCount(1, $this->titles($u2));
        $svc->changeStatus($e, 'dropped');
        $this->assertContains('تغيّرت حالة تسجيلك', $this->titles($u2));
    }

    /** @test */
    public function service_requests_notify_the_officer_on_creation_and_the_student_on_status_change()
    {
        $officer = $this->userWithRole('eservices_officer');
        $this->actingAs($this->studentUser)->post(route('student.service-requests.store'), ['type' => 're_enrollment'])->assertSessionHasNoErrors();
        $this->assertContains('طلب خدمة جديد', $this->titles($officer));

        $request = StudentServiceRequest::latest('id')->first();
        $this->actingAs($officer)->put(route('staff.service-requests.update-status', $request), ['status' => 'in_progress'])->assertSessionHasNoErrors();
        $this->assertContains('تحديث على طلبك', $this->titles($this->studentUser));
    }

    /** @test */
    public function a_notification_failure_never_breaks_the_main_operation()
    {
        $this->assertNull(app(\App\Services\AcademicNotifier::class)->toUser(null, 'x', 'y'));
        $this->assertNull(app(\App\Services\AcademicNotifier::class)->toUser(999999, 'x', 'y')); // مستخدم غير موجود
        $this->assertSame(0, Notification::count());
    }

    /** @test */
    public function the_notifications_appear_in_the_old_notification_pages()
    {
        $reviewer = $this->userWithRole('control');
        $this->actingAs($this->facultyUser)->post(route('exams.submit', $this->exam));
        $this->actingAs($reviewer)->get(route('notifications.index'))->assertOk()->assertSee('نتائج بانتظار المراجعة');
    }
}
