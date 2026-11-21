<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\GradeChange;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class ResultWorkflowTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private Exam $exam;
    private $enrollment;
    private $studentUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->exam = $this->makeExam();
        [$this->studentUser, $student] = $this->makeStudent();
        $this->enrollment = $this->enroll($student);
        $this->setGrade($this->exam, $this->enrollment, 75);
    }

    private function post_(string $route, $user, array $data = [])
    {
        return $this->actingAs($user)->post(route($route, $this->exam), $data);
    }

    /** @test */
    public function the_full_lifecycle_draft_submitted_approved_published()
    {
        $reviewer = $this->userWithPermissions(['results.review']);
        $publisher = $this->userWithPermissions(['results.publish']);

        $this->post_('exams.submit', $this->facultyUser)->assertSessionHasNoErrors();
        $this->assertSame('submitted', $this->exam->fresh()->result_status);
        $this->assertSame($this->facultyUser->id, $this->exam->fresh()->submitted_by);

        $this->post_('exams.approve', $reviewer)->assertSessionHasNoErrors();
        $this->assertSame('approved', $this->exam->fresh()->result_status);
        $this->assertSame($reviewer->id, $this->exam->fresh()->approved_by);

        $this->post_('exams.publish', $publisher)->assertSessionHasNoErrors();
        $fresh = $this->exam->fresh();
        $this->assertSame('published', $fresh->result_status);
        $this->assertSame($publisher->id, $fresh->published_by);
        $this->assertNotNull($fresh->published_at);

        // كل انتقال مسجَّل في سجل العمليات
        $this->assertSame(['exam_results.submit', 'exam_results.approve', 'exam_results.publish'],
            ActivityLog::where('model_type', Exam::class)->orderBy('id')->pluck('action')->all());
    }

    /** @test */
    public function submission_requires_a_score_for_every_actively_enrolled_student()
    {
        [, $second] = $this->makeStudent();
        $this->enroll($second);

        $this->post_('exams.submit', $this->facultyUser)->assertSessionHasErrors('exam');
        $this->assertSame('draft', $this->exam->fresh()->result_status);

        // المنسحب لا يُشترط له درجة
        \App\Models\Enrollment::where('student_profile_id', $second->id)->update(['status' => 'dropped']);
        $this->post_('exams.submit', $this->facultyUser)->assertSessionHasNoErrors();
        $this->assertSame('submitted', $this->exam->fresh()->result_status);
    }

    /** @test */
    public function submission_with_no_enrolled_students_is_rejected()
    {
        $empty = $this->makeExam($this->makeSection($this->faculty));

        $this->actingAs($this->facultyUser)->post(route('exams.submit', $empty))->assertSessionHasErrors('exam');
    }

    /** @test */
    public function rejection_requires_a_reason_and_returns_the_results_to_the_faculty_for_editing()
    {
        $reviewer = $this->userWithPermissions(['results.review']);
        $this->setStatus($this->exam, 'submitted');

        $this->post_('exams.reject', $reviewer)->assertSessionHasErrors('reason');
        $this->post_('exams.reject', $reviewer, ['reason' => 'abc'])->assertSessionHasErrors('reason');
        $this->assertSame('submitted', $this->exam->fresh()->result_status);

        $this->post_('exams.reject', $reviewer, ['reason' => 'درجات غير متسقة مع الورقة'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $this->exam->fresh()->result_status);
        $this->assertSame('درجات غير متسقة مع الورقة', $this->exam->fresh()->rejection_reason);

        // المدرس يعدّل ثم يعيد التقديم
        $this->actingAs($this->facultyUser)->put(route('exams.grades.update', $this->exam), ['scores' => [$this->enrollment->id => 78]])->assertSessionHasNoErrors();
        $this->post_('exams.submit', $this->facultyUser)->assertSessionHasNoErrors();
        $fresh = $this->exam->fresh();
        $this->assertSame('submitted', $fresh->result_status);
        $this->assertNull($fresh->rejection_reason);
    }

    /** @test */
    public function an_approved_result_can_still_be_rejected_before_publication()
    {
        $reviewer = $this->userWithPermissions(['results.review']);
        $this->setStatus($this->exam, 'approved');

        $this->post_('exams.reject', $reviewer, ['reason' => 'خطأ اكتُشف قبل النشر'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $this->exam->fresh()->result_status);
    }

    /** @test */
    public function invalid_transitions_are_refused_even_for_the_admin()
    {
        $admin = $this->userWithRole('admin', 'admin');

        // نشر دون اعتماد
        $this->post_('exams.publish', $admin)->assertSessionHasErrors('exam');
        // اعتماد وهي مسودة
        $this->post_('exams.approve', $admin)->assertSessionHasErrors('exam');
        $this->assertSame('draft', $this->exam->fresh()->result_status);

        $this->setStatus($this->exam, 'published');
        $this->post_('exams.submit', $admin)->assertSessionHasErrors('exam'); // مسموح بالصلاحية، مرفوض بالحالة
        $this->post_('exams.approve', $admin)->assertSessionHasErrors('exam');
        $this->post_('exams.reject', $admin, ['reason' => 'محاولة بعد النشر'])->assertSessionHasErrors('exam');
        $this->assertSame('published', $this->exam->fresh()->result_status);
    }

    /** @test */
    public function the_admin_can_review_and_publish_through_the_existing_gate()
    {
        $admin = $this->userWithRole('admin', 'admin');
        $this->setStatus($this->exam, 'submitted');

        $this->post_('exams.approve', $admin)->assertSessionHasNoErrors();
        $this->post_('exams.publish', $admin)->assertSessionHasNoErrors();
        $this->assertSame('published', $this->exam->fresh()->result_status);
        $this->actingAs($admin)->get(route('results.review'))->assertOk();
        $this->actingAs($admin)->get(route('results.publish'))->assertOk();
    }

    /** @test */
    public function transition_permissions_are_separated()
    {
        $reviewer = $this->userWithPermissions(['results.review']);
        $publisher = $this->userWithPermissions(['results.publish']);

        // المدرس لا يعتمد ولا ينشر ولا يرفض
        $this->setStatus($this->exam, 'submitted');
        $this->post_('exams.approve', $this->facultyUser)->assertForbidden();
        $this->post_('exams.reject', $this->facultyUser, ['reason' => 'ليس من صلاحيتي'])->assertForbidden();
        // المراجع لا ينشر، والناشر لا يعتمد
        $this->setStatus($this->exam, 'approved');
        $this->post_('exams.publish', $reviewer)->assertForbidden();
        $this->setStatus($this->exam, 'submitted');
        $this->post_('exams.approve', $publisher)->assertForbidden();
        // المدرس لا يقدّم نتائج شعبة غيره
        [$otherUser] = $this->makeFaculty();
        $this->setStatus($this->exam, 'draft');
        $this->post_('exams.submit', $otherUser)->assertForbidden();
        // لا أحد يعتمد/ينشر عبر دور student_affairs أو staff أو student بلا صلاحية صريحة
        foreach ([$this->userWithRole('student_affairs'), $this->userWithRole('staff'), $this->studentUser] as $u) {
            $this->post_('exams.approve', $u)->assertForbidden();
            $this->post_('exams.publish', $u)->assertForbidden();
            $this->actingAs($u)->get(route('results.review'))->assertForbidden();
            $this->actingAs($u)->get(route('results.publish'))->assertForbidden();
        }
    }

    /** @test */
    public function the_review_and_publish_pages_list_the_right_exams()
    {
        $reviewer = $this->userWithPermissions(['results.review', 'results.publish']);
        $submitted = $this->exam;
        $this->setStatus($submitted, 'submitted');
        $approved = $this->makeExam(null, ['result_status' => 'approved']);
        $draft = $this->makeExam(null, ['result_status' => 'draft']);

        $review = $this->actingAs($reviewer)->get(route('results.review'))->assertOk()->viewData('exams');
        $this->assertEqualsCanonicalizing([$submitted->id, $approved->id], $review->pluck('id')->all());
        $publish = $this->actingAs($reviewer)->get(route('results.publish'))->assertOk()->viewData('exams');
        $this->assertSame([$approved->id], $publish->pluck('id')->all());
    }

    /** @test */
    public function published_grades_cannot_be_edited_directly_but_can_be_corrected_with_a_logged_reason()
    {
        $this->setStatus($this->exam, 'published');
        $grade = Grade::first();
        $corrector = $this->userWithPermissions(['grades.correct']);

        // تعديل مباشر مرفوض
        $this->actingAs($this->facultyUser)->put(route('exams.grades.update', $this->exam), ['scores' => [$this->enrollment->id => 99]])->assertSessionHasErrors('exam');
        $this->assertEquals(75, $grade->fresh()->score);

        // المدرس لا يملك التصحيح
        $url = route('exams.grades.correct', [$this->exam, $grade]);
        $this->actingAs($this->facultyUser)->post($url, ['new_score' => 80, 'reason' => 'خطأ جمع في الورقة'])->assertForbidden();

        // بلا سبب مرفوض، وفوق الحد مرفوض
        $this->actingAs($corrector)->post($url, ['new_score' => 80])->assertSessionHasErrors('reason');
        $this->actingAs($corrector)->post($url, ['new_score' => 101, 'reason' => 'خطأ جمع في الورقة'])->assertSessionHasErrors('new_score');
        $this->assertEquals(75, $grade->fresh()->score);

        // التصحيح الصحيح: الدرجة تتغير والسجل يحفظ القيمة السابقة والسبب والمستخدم
        $this->actingAs($corrector)->post($url, ['new_score' => 80, 'reason' => 'خطأ جمع في الورقة'])->assertSessionHasNoErrors();
        $this->assertEquals(80, $grade->fresh()->score);
        $change = GradeChange::where('action', 'corrected')->firstOrFail();
        $this->assertEquals(75, $change->old_score);
        $this->assertEquals(80, $change->new_score);
        $this->assertSame('خطأ جمع في الورقة', $change->reason);
        $this->assertSame($corrector->id, $change->changed_by);
        $this->assertSame('published', $this->exam->fresh()->result_status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'grade.corrected', 'user_id' => $corrector->id]);
    }

    /** @test */
    public function corrections_are_not_available_for_drafts_or_submitted_results_and_not_across_exams()
    {
        $corrector = $this->userWithPermissions(['grades.correct']);
        $grade = Grade::first();
        $url = route('exams.grades.correct', [$this->exam, $grade]);

        foreach (['draft', 'submitted', 'rejected'] as $status) {
            $this->setStatus($this->exam, $status);
            $this->actingAs($corrector)->post($url, ['new_score' => 80, 'reason' => 'محاولة تصحيح خاطئة'])->assertSessionHasErrors('exam');
        }
        $this->assertEquals(75, $grade->fresh()->score);

        // درجة تخص اختبارًا آخر عبر مسار هذا الاختبار → 404
        $this->setStatus($this->exam, 'published');
        $otherExam = $this->makeExam();
        $otherGrade = $this->setGrade($otherExam, $this->enrollment, 10);
        $this->actingAs($corrector)->post(route('exams.grades.correct', [$this->exam, $otherGrade]), ['new_score' => 80, 'reason' => 'محاولة عبر اختبار آخر'])->assertNotFound();
    }

    /** @test */
    public function permissions_are_seeded_without_leaking_to_unrelated_roles()
    {
        $holders = fn (string $perm) => Role::whereHas('permissions', fn ($q) => $q->where('slug', $perm))->pluck('slug')->sort()->values()->all();

        $this->assertSame(['faculty'], $holders('exams.manageOwn'));
        $this->assertSame(['faculty'], $holders('grades.enterOwn'));
        $this->assertSame(['student'], $holders('results.viewOwn'));
        // دور الكنترول وحده يحمل صلاحيات اعتماد النتائج؛ والسياسات الأكاديمية تبقى للمدير
        foreach (['exams.manage', 'results.review', 'results.publish', 'grades.correct', 'academic-records.viewAll'] as $perm) {
            $this->assertSame(['control'], $holders($perm), "$perm للكنترول وحده");
        }
        $this->assertSame([], $holders('academic-settings.manage'));

        // إعادة تشغيل الـ seeder آمنة (idempotent)
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->assertSame(['faculty'], $holders('exams.manageOwn'));
    }
}
