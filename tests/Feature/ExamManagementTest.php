<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class ExamManagementTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    /** @test */
    public function a_faculty_member_can_create_and_update_an_exam_in_their_own_section()
    {
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload())
            ->assertSessionHasNoErrors();

        $exam = Exam::firstOrFail();
        $this->assertSame('draft', $exam->result_status);
        $this->assertSame($this->facultyUser->id, $exam->created_by);
        $this->assertSame($this->section->id, $exam->section_id);

        $this->actingAs($this->facultyUser)->put(route('exams.update', $exam), $this->validExamPayload(['name' => 'نصفي معدّل', 'max_score' => 60]))
            ->assertSessionHasNoErrors();
        $this->assertSame('نصفي معدّل', $exam->fresh()->name);
        $this->assertEquals(60, $exam->fresh()->max_score);
    }

    /** @test */
    public function all_four_exam_types_are_supported_and_unknown_types_rejected()
    {
        foreach (['midterm', 'final', 'quiz', 'practical'] as $i => $type) {
            $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => "T$i", 'type' => $type, 'start_time' => null]))
                ->assertSessionHasNoErrors();
        }
        $this->assertSame(4, Exam::count());

        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'X', 'type' => 'oral']))
            ->assertSessionHasErrors('type');
    }

    /** @test */
    public function a_faculty_member_cannot_manage_exams_of_a_section_that_is_not_theirs()
    {
        [$otherUser, $otherFaculty] = $this->makeFaculty();
        $foreign = $this->makeSection($otherFaculty);
        $foreignExam = $this->makeExam($foreign);

        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['section_id' => $foreign->id]))
            ->assertSessionHasErrors('section_id');
        $this->assertSame(1, Exam::count());

        $this->actingAs($this->facultyUser)->get(route('exams.show', $foreignExam))->assertForbidden();
        $this->actingAs($this->facultyUser)->put(route('exams.update', $foreignExam), $this->validExamPayload(['section_id' => $foreign->id]))->assertForbidden();
        $this->actingAs($this->facultyUser)->delete(route('exams.destroy', $foreignExam))->assertForbidden();
        $this->actingAs($this->facultyUser)->get(route('exams.grades.edit', $foreignExam))->assertForbidden();
        $this->assertDatabaseHas('exams', ['id' => $foreignExam->id]);
    }

    /** @test */
    public function students_ordinary_staff_student_affairs_and_guests_cannot_manage_exams()
    {
        [$studentUser] = $this->makeStudent();
        $exam = $this->makeExam();

        foreach ([$studentUser, $this->userWithRole('staff'), $this->userWithRole('student_affairs')] as $user) {
            $this->actingAs($user)->get(route('exams.index'))->assertForbidden();
            $this->actingAs($user)->post(route('exams.store'), $this->validExamPayload())->assertForbidden();
            $this->actingAs($user)->get(route('exams.show', $exam))->assertForbidden();
            $this->actingAs($user)->delete(route('exams.destroy', $exam))->assertForbidden();
        }

        auth()->logout();
        $this->get(route('exams.index'))->assertRedirect(route('login'));
        $this->assertSame(1, Exam::count());
    }

    /** @test */
    public function the_admin_can_manage_exams_of_any_section()
    {
        $admin = $this->userWithRole('admin', 'admin');

        $this->actingAs($admin)->post(route('exams.store'), $this->validExamPayload())->assertSessionHasNoErrors();
        $this->assertSame(1, Exam::count());
        $this->actingAs($admin)->get(route('exams.index'))->assertOk();
    }

    /** @test */
    public function the_exams_manage_permission_allows_managing_any_section_but_not_entering_grades()
    {
        $manager = $this->userWithPermissions(['exams.manage']);
        $exam = $this->makeExam();

        $this->actingAs($manager)->post(route('exams.store'), $this->validExamPayload(['name' => 'جديد']))->assertSessionHasNoErrors();
        $this->actingAs($manager)->get(route('exams.show', $exam))->assertOk();
        $this->actingAs($manager)->put(route('exams.grades.update', $exam), ['scores' => [1 => 5]])->assertForbidden();
    }

    /** @test */
    public function exam_data_is_validated_logically()
    {
        $post = fn (array $o) => $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload($o + ['name' => 'N'.uniqid()]));

        $post(['max_score' => 0])->assertSessionHasErrors('max_score');
        $post(['max_score' => -5])->assertSessionHasErrors('max_score');
        $post(['weight' => -1])->assertSessionHasErrors('weight');
        $post(['duration_minutes' => 0])->assertSessionHasErrors('duration_minutes');
        $post(['exam_date' => 'not-a-date'])->assertSessionHasErrors('exam_date');
        $post(['max_score' => 10.555])->assertSessionHasErrors('max_score');
        $this->assertSame(0, Exam::count());
    }

    /** @test */
    public function duplicate_names_in_the_same_section_are_rejected_but_allowed_elsewhere()
    {
        $this->makeExam($this->section, ['name' => 'نصفي']);
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'نصفي', 'start_time' => null]))
            ->assertSessionHasErrors('name');

        $other = $this->makeSection($this->faculty);
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['section_id' => $other->id, 'name' => 'نصفي', 'start_time' => null]))
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function overlapping_exams_for_the_same_section_are_rejected()
    {
        $this->makeExam($this->section, ['name' => 'أ', 'exam_date' => '2026-11-10', 'start_time' => '09:00', 'duration_minutes' => 90]);

        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'ب', 'start_time' => '10:00']))
            ->assertSessionHasErrors('start_time');
        // بعد انتهاء الأول مباشرة مقبول
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'ج', 'start_time' => '10:30']))
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function the_exam_date_must_fall_inside_the_term_when_the_term_has_dates()
    {
        $this->term->update(['start_date' => '2026-09-01', 'end_date' => '2026-12-31']);

        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['exam_date' => '2027-02-01', 'start_time' => null]))
            ->assertSessionHasErrors('exam_date');
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'ب', 'exam_date' => '2026-08-01', 'start_time' => null]))
            ->assertSessionHasErrors('exam_date');
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'ج', 'exam_date' => '2026-11-20', 'start_time' => null]))
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function max_score_cannot_drop_below_an_already_recorded_grade()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $this->setGrade($exam, $this->enroll($student), 80);

        $this->actingAs($this->facultyUser)->put(route('exams.update', $exam), $this->validExamPayload(['name' => $exam->name, 'max_score' => 70, 'start_time' => null]))
            ->assertSessionHasErrors('max_score');
        $this->assertEquals(100, $exam->fresh()->max_score);
    }

    /** @test */
    public function an_exam_with_grades_or_locked_results_cannot_be_deleted_or_edited()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $this->setGrade($exam, $this->enroll($student), 50);

        $this->actingAs($this->facultyUser)->delete(route('exams.destroy', $exam))->assertSessionHasErrors('exam');
        $this->assertDatabaseHas('exams', ['id' => $exam->id]);

        $exam->update(['result_status' => 'submitted']);
        $this->actingAs($this->facultyUser)->put(route('exams.update', $exam), $this->validExamPayload(['name' => $exam->name, 'start_time' => null]))
            ->assertSessionHasErrors('exam');

        // حتى المدير لا يتجاوز قفل الحالة
        $admin = $this->userWithRole('admin', 'admin');
        $this->actingAs($admin)->put(route('exams.update', $exam), $this->validExamPayload(['name' => 'تغيير', 'start_time' => null]))->assertSessionHasErrors('exam');
        $this->assertNotSame('تغيير', $exam->fresh()->name);
    }

    /** @test */
    public function an_empty_draft_exam_can_be_deleted()
    {
        $exam = $this->makeExam();

        $this->actingAs($this->facultyUser)->delete(route('exams.destroy', $exam))->assertRedirect(route('exams.index'));
        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    /** @test */
    public function exams_can_be_filtered_by_term_course_and_section_and_faculty_sees_only_theirs()
    {
        $otherTerm = AcademicTerm::create(['academic_year_id' => $this->term->academic_year_id, 'semester' => 'second']);
        $course2 = $this->makeCourse('C2');
        $s2 = \App\Models\Section::create(['course_id' => $course2->id, 'academic_term_id' => $otherTerm->id, 'faculty_profile_id' => $this->faculty->id, 'code' => 'Q', 'capacity' => 10]);
        [, $otherFaculty] = $this->makeFaculty();
        $foreign = $this->makeSection($otherFaculty);

        $a = $this->makeExam($this->section);
        $b = $this->makeExam($s2);
        $this->makeExam($foreign);

        $ids = fn ($r) => $r->viewData('exams')->pluck('id')->sort()->values()->all();
        $this->assertSame([$a->id, $b->id], $ids($this->actingAs($this->facultyUser)->get(route('exams.index'))->assertOk()));
        $this->assertSame([$b->id], $ids($this->actingAs($this->facultyUser)->get(route('exams.index', ['term' => $otherTerm->id]))));
        $this->assertSame([$b->id], $ids($this->actingAs($this->facultyUser)->get(route('exams.index', ['course' => $course2->id]))));
        $this->assertSame([$a->id], $ids($this->actingAs($this->facultyUser)->get(route('exams.index', ['section' => $this->section->id]))));
    }
}
