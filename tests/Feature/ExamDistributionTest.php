<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\AcademicTerm;
use App\Models\Exam;
use App\Models\ExamDistributionChange;
use App\Models\Section;
use App\Services\ExamResultWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * Phase 7 v2 — توزيع الدرجات خاص بكل شعبة/دكتور: يُقفل بعد التقديم، ويُعدَّل إداريًا بسبب فقط.
 */
class ExamDistributionTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->admin = $this->userWithRole('admin');
    }

    private function submitSection(Section $section, $facultyUser): Exam
    {
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student, $section);
        $exam = $this->makeExam($section, ['weight' => 40]);
        $this->setGrade($exam, $enrollment, 70);
        app(ExamResultWorkflow::class)->submit($exam, $facultyUser);

        return $exam->fresh();
    }

    /** @test */
    public function the_distribution_is_open_before_submission_and_locked_after()
    {
        $this->assertFalse($this->section->distributionLocked());
        $exam = $this->submitSection($this->section, $this->facultyUser);
        $this->assertTrue($this->section->distributionLocked());

        $payload = $this->validExamPayload(['name' => 'نهائي', 'weight' => 60, 'start_time' => '13:00']);
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $payload)->assertSessionHasErrors('exam');
        $this->actingAs($this->facultyUser)->put(route('exams.update', $exam), $payload)->assertSessionHasErrors('exam');
        $this->actingAs($this->facultyUser)->delete(route('exams.destroy', $exam))->assertSessionHasErrors('exam');
        $this->assertDatabaseMissing('exams', ['name' => 'نهائي']);
        $this->assertEquals(40, $exam->fresh()->weight);
    }

    /** @test */
    public function a_draft_exam_in_a_locked_section_cannot_be_modified_either()
    {
        $this->submitSection($this->section, $this->facultyUser);
        $draft = $this->makeExam(null, ['name' => 'مسودة', 'weight' => 10]);

        $this->actingAs($this->facultyUser)->put(route('exams.update', $draft), $this->validExamPayload(['name' => 'مسودة', 'weight' => 99]))->assertSessionHasErrors('exam');
        $this->assertEquals(10, $draft->fresh()->weight);
    }

    /** @test */
    public function rejecting_the_only_submitted_exam_reopens_the_distribution()
    {
        $exam = $this->submitSection($this->section, $this->facultyUser);
        app(ExamResultWorkflow::class)->reject($exam, $this->admin, 'مراجعة');

        $this->assertFalse($this->section->fresh()->distributionLocked());
    }

    /** @test */
    public function administrative_adjustment_requires_a_reason_and_the_dedicated_permission()
    {
        $exam = $this->submitSection($this->section, $this->facultyUser);
        $url = route('exams.distribution.update', $exam);
        $payload = ['name' => $exam->name, 'type' => 'midterm', 'max_score' => 100, 'weight' => 55];

        $this->actingAs($this->facultyUser)->put($url, $payload + ['reason' => 'محاولة من الدكتور'])->assertForbidden();

        $officer = $this->userWithPermissions(['exams.adjustDistribution']);
        $this->actingAs($officer)->put($url, $payload)->assertSessionHasErrors('reason');
        $this->assertEquals(40, $exam->fresh()->weight);

        $this->actingAs($officer)->put($url, $payload + ['reason' => 'ts'])->assertSessionHasErrors('reason');
        $this->assertEquals(40, $exam->fresh()->weight);
    }

    /** @test */
    public function administrative_adjustment_records_old_and_new_values_with_the_reason()
    {
        $exam = $this->submitSection($this->section, $this->facultyUser);
        $officer = $this->userWithPermissions(['exams.adjustDistribution']);

        $this->actingAs($officer)->put(route('exams.distribution.update', $exam), [
            'name' => $exam->name, 'type' => 'final', 'max_score' => 80, 'weight' => 55, 'reason' => 'قرار لجنة الكنترول رقم 5',
        ])->assertSessionHasNoErrors();

        $exam->refresh();
        $this->assertEquals(55, $exam->weight);
        $this->assertEquals(80, $exam->max_score);
        $this->assertSame('final', $exam->type);

        $change = ExamDistributionChange::firstOrFail();
        $this->assertSame('updated', $change->action);
        $this->assertSame($officer->id, $change->changed_by);
        $this->assertSame($this->section->id, $change->section_id);
        $this->assertSame('قرار لجنة الكنترول رقم 5', $change->reason);
        $this->assertEquals(40, $change->old_values['weight']);
        $this->assertEquals(100, $change->old_values['max_score']);
        $this->assertSame('midterm', $change->old_values['type']);
        $this->assertEquals(55, $change->new_values['weight']);
        $this->assertEquals(80, $change->new_values['max_score']);
        $this->assertSame('final', $change->new_values['type']);
    }

    /** @test */
    public function administrative_adjustment_cannot_set_max_score_below_an_existing_grade()
    {
        $exam = $this->submitSection($this->section, $this->facultyUser); // درجة 70
        $officer = $this->userWithPermissions(['exams.adjustDistribution']);

        $this->actingAs($officer)->put(route('exams.distribution.update', $exam), [
            'name' => $exam->name, 'type' => 'midterm', 'max_score' => 50, 'weight' => 40, 'reason' => 'محاولة خاطئة',
        ])->assertSessionHasErrors('max_score');

        $this->assertEquals(100, $exam->fresh()->max_score);
        $this->assertSame(0, ExamDistributionChange::count());
    }

    /** @test */
    public function administrative_creation_and_deletion_are_logged()
    {
        $this->submitSection($this->section, $this->facultyUser);
        $officer = $this->userWithPermissions(['exams.adjustDistribution']);

        $this->actingAs($officer)->post(route('exams.distribution.store'), [
            'section_id' => $this->section->id, 'name' => 'اختبار إضافي', 'type' => 'quiz', 'max_score' => 20, 'weight' => 10,
            'exam_date' => '2026-11-20', 'duration_minutes' => 30, 'reason' => 'اختبار تعويضي معتمد',
        ])->assertSessionHasNoErrors();

        $added = Exam::where('name', 'اختبار إضافي')->firstOrFail();
        $this->assertDatabaseHas('exam_distribution_changes', ['exam_id' => $added->id, 'action' => 'created', 'reason' => 'اختبار تعويضي معتمد']);

        $this->actingAs($officer)->delete(route('exams.distribution.destroy', $added), ['reason' => 'أُلغي القرار'])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('exams', ['id' => $added->id]);
        $this->assertDatabaseHas('exam_distribution_changes', ['section_id' => $this->section->id, 'action' => 'deleted', 'reason' => 'أُلغي القرار']);
    }

    /** @test */
    public function an_exam_with_grades_cannot_be_deleted_administratively()
    {
        $exam = $this->submitSection($this->section, $this->facultyUser);
        $officer = $this->userWithPermissions(['exams.adjustDistribution']);

        $this->actingAs($officer)->delete(route('exams.distribution.destroy', $exam), ['reason' => 'محاولة حذف'])->assertSessionHasErrors('exam');
        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
    }

    /** @test */
    public function different_doctors_may_use_different_distributions_independently()
    {
        [$userB, $facultyB] = $this->makeFaculty();
        $sectionB = $this->makeSection($facultyB);

        // الدكتور أ: وزنان 40+30 = 70 — الدكتور ب: 50+70 = 120
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'نصفي', 'weight' => 40]))->assertSessionHasNoErrors();
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'نهائي', 'weight' => 30, 'start_time' => '13:00']))->assertSessionHasNoErrors();
        $this->actingAs($userB)->post(route('exams.store'), $this->validExamPayload(['section_id' => $sectionB->id, 'name' => 'نصفي', 'weight' => 50]))->assertSessionHasNoErrors();
        $this->actingAs($userB)->post(route('exams.store'), $this->validExamPayload(['section_id' => $sectionB->id, 'name' => 'نهائي', 'weight' => 70, 'start_time' => '13:00']))->assertSessionHasNoErrors();

        $this->assertEquals(70, $this->section->exams()->sum('weight'));
        $this->assertEquals(120, $sectionB->exams()->sum('weight'));
        // دكتور لا يستطيع إنشاء اختبار في شعبة زميله
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['section_id' => $sectionB->id, 'name' => 'تسلل']))->assertSessionHasErrors('section_id');
    }

    /** @test */
    public function locking_or_adjusting_one_section_never_affects_another_section()
    {
        [$userB, $facultyB] = $this->makeFaculty();
        $sectionB = $this->makeSection($facultyB);
        $examA = $this->submitSection($this->section, $this->facultyUser);
        $examB = $this->makeExam($sectionB, ['weight' => 25]);

        $this->assertTrue($this->section->distributionLocked());
        $this->assertFalse($sectionB->distributionLocked());

        // الشعبة ب ما زالت قابلة للتعديل المباشر
        $this->actingAs($userB)->put(route('exams.update', $examB), $this->validExamPayload(['section_id' => $sectionB->id, 'name' => $examB->name, 'weight' => 35]))->assertSessionHasNoErrors();
        $this->assertEquals(35, $examB->fresh()->weight);

        // تعديل إداري على الشعبة أ لا يلمس ب
        $officer = $this->userWithPermissions(['exams.adjustDistribution']);
        $this->actingAs($officer)->put(route('exams.distribution.update', $examA), ['name' => $examA->name, 'type' => 'midterm', 'max_score' => 100, 'weight' => 90, 'reason' => 'تعديل اللجنة'])->assertSessionHasNoErrors();
        $this->assertEquals(90, $examA->fresh()->weight);
        $this->assertEquals(35, $examB->fresh()->weight);
        $this->assertSame(0, ExamDistributionChange::where('section_id', $sectionB->id)->count());
    }

    /** @test */
    public function adjusting_a_section_does_not_change_the_results_of_a_previous_term()
    {
        // فصل سابق منشور بوزن 40
        $prevTerm = AcademicTerm::create(['academic_year_id' => $this->term->academic_year_id, 'semester' => 'summer']);
        $prevSection = Section::create(['course_id' => $this->makeCourse('P1')->id, 'academic_term_id' => $prevTerm->id, 'faculty_profile_id' => $this->faculty->id, 'code' => 'PV', 'capacity' => 5]);
        [$user, $student] = $this->makeStudent();
        $prevEnroll = $this->enroll($student, $prevSection);
        $prevExam = $this->makeExam($prevSection, ['weight' => 40, 'max_score' => 100, 'result_status' => 'published', 'published_at' => now()]);
        $this->setGrade($prevExam, $prevEnroll, 60);

        $before = $this->actingAs($user)->get(route('student.academic-record'))->viewData('record');
        $prevPct = collect($before['terms'])->flatMap->courses->firstWhere('course.code', 'P1')['percentage'];

        // تعديل إداري في الفصل الحالي
        $current = $this->submitSection($this->section, $this->facultyUser);
        $officer = $this->userWithPermissions(['exams.adjustDistribution']);
        $this->actingAs($officer)->put(route('exams.distribution.update', $current), ['name' => $current->name, 'type' => 'midterm', 'max_score' => 100, 'weight' => 5, 'reason' => 'تعديل اللجنة'])->assertSessionHasNoErrors();

        $after = $this->actingAs($user)->get(route('student.academic-record'))->viewData('record');
        $this->assertEquals($prevPct, collect($after['terms'])->flatMap->courses->firstWhere('course.code', 'P1')['percentage']);
        $this->assertEquals(40, $prevExam->fresh()->weight);
    }

    /** @test */
    public function there_is_no_global_setting_for_weights_or_distribution()
    {
        $manager = $this->userWithPermissions(['academic-settings.manage']);

        $this->actingAs($manager)->put(route('academic-settings.update'), [
            'pass_percentage' => 50, 'default_weights' => [40, 60], 'weight' => 100, 'distribution' => 'uniform',
        ])->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(
            [], array_diff(AcademicSetting::pluck('key')->all(), [AcademicSetting::PASS_PERCENTAGE, AcademicSetting::GRADE_SCALE, AcademicSetting::APPEAL_WINDOW_DAYS])
        );
    }
}
