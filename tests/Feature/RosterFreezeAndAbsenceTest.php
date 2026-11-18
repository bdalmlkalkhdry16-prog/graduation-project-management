<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamRosterEntry;
use App\Models\Grade;
use App\Models\GradeChange;
use App\Services\ExamResultWorkflow;
use App\Services\GradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * Phase 7 v2 — تثبيت القائمة، حماية السجل الأكاديمي، سياسة الغياب.
 */
class RosterFreezeAndAbsenceTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private ExamResultWorkflow $workflow;
    private GradeService $grades;
    private Exam $exam;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->workflow = app(ExamResultWorkflow::class);
        $this->grades = app(GradeService::class);
        $this->exam = $this->makeExam(null, ['weight' => 100]);
        $this->admin = $this->userWithRole('admin');
    }

    private function students(int $n): array
    {
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            [$user, $profile] = $this->makeStudent();
            $out[] = [$user, $profile, $this->enroll($profile)];
        }

        return $out;
    }

    private function approve(Exam $exam): void
    {
        $this->workflow->submit($exam, $this->facultyUser);
        $this->workflow->approve($exam, $this->admin);
    }

    /** @test */
    public function submitting_freezes_exactly_the_active_roster()
    {
        [[, , $a], [, , $b]] = $this->students(2);
        [, $dropProfile] = $this->makeStudent();
        $this->enroll($dropProfile, null, 'dropped');
        $this->setGrade($this->exam, $a, 50);
        $this->setGrade($this->exam, $b, 60);

        $this->workflow->submit($this->exam, $this->facultyUser);

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $this->exam->rosterEntries()->pluck('enrollment_id')->all());
        $this->assertSame($this->facultyUser->id, $this->exam->rosterEntries()->first()->frozen_by);
    }

    /** @test */
    public function the_frozen_roster_ignores_later_withdrawals_and_new_enrollments_through_publication()
    {
        [[, , $a], [$bUser, $bProfile, $b]] = $this->students(2);
        $this->setGrade($this->exam, $a, 50);
        $this->setGrade($this->exam, $b, 60);
        $this->approve($this->exam);

        // بعد التقديم: انسحاب طالب وتسجيل طالب جديد
        $b->update(['status' => 'dropped']);
        [, , $late] = $this->students(1)[0];
        $frozenIds = $this->exam->rosterEntries()->pluck('enrollment_id')->all();

        $this->workflow->publish($this->exam, $this->admin);

        $this->assertSame('published', $this->exam->fresh()->result_status);
        $this->assertEqualsCanonicalizing($frozenIds, $this->exam->rosterEntries()->pluck('enrollment_id')->all());
        $this->assertNotContains($late->id, $frozenIds);
        // قائمة الرصد/العرض تبقى المثبتة (تتضمن المنسحب ولا تتضمن الجديد)
        $ids = $this->grades->roster($this->exam->fresh())->pluck('id')->all();
        $this->assertContains($b->id, $ids);
        $this->assertNotContains($late->id, $ids);
    }

    /** @test */
    public function results_of_a_student_who_later_withdrew_stay_in_results_and_academic_record()
    {
        [[, , $a], [$bUser, $bProfile, $b]] = $this->students(2);
        $this->setGrade($this->exam, $a, 50);
        $this->setGrade($this->exam, $b, 80);
        $this->approve($this->exam);
        $this->workflow->publish($this->exam, $this->admin);

        $b->update(['status' => 'dropped']);

        $results = $this->actingAs($bUser)->get(route('student.results.index'))->assertOk()->viewData('grades');
        $this->assertCount(1, $results);
        $this->assertEquals(80, $results->first()->score);

        $course = $this->actingAs($bUser)->get(route('student.academic-record'))->assertOk()->viewData('record')['terms'][0]['courses'][0];
        $this->assertTrue($course['dropped']);
        $this->assertEquals(80, $course['percentage']);
        $this->assertDatabaseHas('grades', ['enrollment_id' => $b->id, 'score' => 80]);
    }

    /** @test */
    public function a_dropped_enrollment_without_published_results_is_not_shown_in_the_record()
    {
        [[$user, , $a]] = $this->students(1);
        $this->setGrade($this->exam, $a, 40); // مسودة فقط
        $a->update(['status' => 'dropped']);

        $this->assertSame([], $this->actingAs($user)->get(route('student.academic-record'))->viewData('record')['terms']);
    }

    /** @test */
    public function a_grade_of_a_student_outside_the_frozen_roster_is_never_published_or_counted()
    {
        [[, , $a], [$cUser, , $c]] = $this->students(2);
        $this->setGrade($this->exam, $a, 50);
        $this->setGrade($this->exam, $c, 90);
        $c->update(['status' => 'dropped']);       // انسحب قبل التقديم
        $this->approve($this->exam);
        $this->workflow->publish($this->exam, $this->admin);

        $this->assertNotContains($c->id, $this->exam->rosterEntries()->pluck('enrollment_id')->all());
        $this->assertCount(0, $this->actingAs($cUser)->get(route('student.results.index'))->viewData('grades'));
        $this->assertSame([], $this->actingAs($cUser)->get(route('student.academic-record'))->viewData('record')['terms']);
    }

    /** @test */
    public function rejection_releases_the_roster_and_resubmission_freezes_the_current_one()
    {
        [[, , $a], [, , $b]] = $this->students(2);
        $this->setGrade($this->exam, $a, 50);
        $this->setGrade($this->exam, $b, 60);
        $this->workflow->submit($this->exam, $this->facultyUser);
        $this->assertSame(2, $this->exam->rosterEntries()->count());

        $this->workflow->reject($this->exam, $this->admin, 'مراجعة الدرجات');
        $this->assertSame(0, $this->exam->rosterEntries()->count());

        $b->update(['status' => 'dropped']);
        $this->workflow->submit($this->exam->fresh(), $this->facultyUser);

        $this->assertSame([$a->id], $this->exam->rosterEntries()->pluck('enrollment_id')->all());
    }

    /** @test */
    public function approval_and_publication_fail_when_the_frozen_roster_is_tampered_with()
    {
        [[, , $a]] = $this->students(1);
        $this->setGrade($this->exam, $a, 50);
        $this->workflow->submit($this->exam, $this->facultyUser);

        // قائمة محذوفة
        $this->exam->rosterEntries()->delete();
        $this->expectValidation(fn () => $this->workflow->approve($this->exam->fresh(), $this->admin));

        // تسجيل من شعبة أخرى داخل القائمة
        $other = $this->makeSection($this->faculty);
        [, $p] = $this->makeStudent();
        $foreign = $this->enroll($p, $other);
        ExamRosterEntry::create(['exam_id' => $this->exam->id, 'enrollment_id' => $foreign->id]);
        ExamRosterEntry::create(['exam_id' => $this->exam->id, 'enrollment_id' => $a->id]);
        $this->expectValidation(fn () => $this->workflow->approve($this->exam->fresh(), $this->admin));
        $this->assertSame('submitted', $this->exam->fresh()->result_status);
    }

    /** @test */
    public function grade_service_rejects_enrollments_of_another_section_and_outsiders_after_submission()
    {
        [[, , $a]] = $this->students(1);
        $other = $this->makeSection($this->faculty);
        [, $p] = $this->makeStudent();
        $foreign = $this->enroll($p, $other);

        // أثناء الرصد: تسجيل من شعبة أخرى
        $this->expectValidation(fn () => $this->grades->saveScores($this->exam, [$foreign->id => 10], $this->facultyUser));
        $this->expectValidation(fn () => $this->grades->saveScores($this->exam, [], $this->facultyUser, [$foreign->id => 1]));
        $this->assertSame(0, Grade::where('enrollment_id', $foreign->id)->count());

        // بعد التقديم والاعتماد: تصحيح لدرجة طالب خارج القائمة المثبتة
        $this->setGrade($this->exam, $a, 50);
        $this->approve($this->exam);
        [, , $late] = $this->students(1)[0];
        $stray = Grade::create(['exam_id' => $this->exam->id, 'enrollment_id' => $late->id, 'score' => 10]);
        $this->expectValidation(fn () => $this->grades->correct($stray, 20, 'محاولة تصحيح', $this->admin));
        $this->assertEquals(10, $stray->fresh()->score);

        // وحفظ الدرجات مقفل بعد التقديم
        $this->expectValidation(fn () => $this->grades->saveScores($this->exam->fresh(), [$late->id => 10], $this->facultyUser));
    }

    /** @test */
    public function absence_is_recorded_as_unresolved_and_never_converted_to_zero()
    {
        [[, , $a], [, , $b]] = $this->students(2);

        $this->grades->saveScores($this->exam, [$a->id => 70], $this->facultyUser, [$b->id => '1']);

        $grade = Grade::where('enrollment_id', $b->id)->firstOrFail();
        $this->assertNull($grade->score);
        $this->assertSame('absent_unresolved', $grade->status);
        $this->assertTrue($grade->isAbsentUnresolved());
        $this->assertDatabaseHas('grade_changes', ['grade_id' => $grade->id, 'new_status' => 'absent_unresolved', 'new_score' => null]);
    }

    /** @test */
    public function a_score_and_an_absence_for_the_same_student_are_rejected()
    {
        [[, , $a]] = $this->students(1);

        $this->expectValidation(fn () => $this->grades->saveScores($this->exam, [$a->id => 10], $this->facultyUser, [$a->id => '1']));
        $this->assertSame(0, Grade::count());
    }

    /** @test */
    public function submission_requires_a_score_or_a_recorded_absence_for_every_student()
    {
        [[, , $a], [, , $b]] = $this->students(2);
        $this->setGrade($this->exam, $a, 50);

        $this->expectValidation(fn () => $this->workflow->submit($this->exam, $this->facultyUser));

        $this->absent($this->exam, $b);
        $this->workflow->submit($this->exam->fresh(), $this->facultyUser);
        $this->assertSame('submitted', $this->exam->fresh()->result_status);
    }

    /** @test */
    public function an_exam_with_unresolved_absence_cannot_be_published_until_resolved_by_correction()
    {
        [[, , $a], [, , $b]] = $this->students(2);
        $this->setGrade($this->exam, $a, 50);
        $absence = $this->absent($this->exam, $b);
        $this->approve($this->exam);

        $this->expectValidation(fn () => $this->workflow->publish($this->exam->fresh(), $this->admin));
        $this->assertSame('approved', $this->exam->fresh()->result_status);
        $this->assertNull($this->exam->fresh()->published_at);

        // محاولة عبر الواجهة أيضًا
        $this->actingAs($this->admin)->post(route('exams.publish', $this->exam))->assertSessionHasErrors('exam');

        // الحسم: تصحيح معتمد بإدخال درجة صريحة
        $this->grades->correct($absence, 0, 'غياب بعذر — حُسم بدرجة صريحة', $this->admin);
        $absence->refresh();
        $this->assertSame('scored', $absence->status);
        $this->assertEquals(0, $absence->score);
        $this->assertDatabaseHas('grade_changes', ['grade_id' => $absence->id, 'action' => 'corrected', 'old_status' => 'absent_unresolved', 'new_status' => 'scored']);

        $this->workflow->publish($this->exam->fresh(), $this->admin);
        $this->assertSame('published', $this->exam->fresh()->result_status);
    }

    /** @test */
    public function unresolved_absence_never_appears_as_a_published_score_for_students()
    {
        [[$user, , $a]] = $this->students(1);
        $this->absent($this->exam, $a);
        $this->setStatus($this->exam, 'published'); // حالة شاذة مفروضة مباشرة

        $this->assertCount(0, $this->actingAs($user)->get(route('student.results.index'))->viewData('grades'));
        $course = $this->actingAs($user)->get(route('student.academic-record'))->viewData('record')['terms'][0]['courses'][0];
        $this->assertNull($course['percentage']);
        $this->assertFalse($course['complete']);
    }

    private function expectValidation(\Closure $fn): void
    {
        try {
            $fn();
            $this->fail('كان متوقعًا ValidationException');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }
}
