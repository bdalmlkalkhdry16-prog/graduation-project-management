<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\GradeChange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class GradeEntryTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    private function save($exam, array $scores, $user = null)
    {
        return $this->actingAs($user ?? $this->facultyUser)->put(route('exams.grades.update', $exam), ['scores' => $scores]);
    }

    /** @test */
    public function the_entry_page_lists_only_actively_enrolled_students()
    {
        $exam = $this->makeExam();
        [, $active] = $this->makeStudent('طالب نشط');
        [, $dropped] = $this->makeStudent('طالب منسحب');
        [, $completed] = $this->makeStudent('طالب مكتمل');
        [, $other] = $this->makeStudent('طالب شعبة أخرى');
        $this->enroll($active);
        $this->enroll($dropped, null, 'dropped');
        $this->enroll($completed, null, 'completed');
        $this->enroll($other, $this->makeSection($this->faculty));

        $r = $this->actingAs($this->facultyUser)->get(route('exams.grades.edit', $exam))->assertOk();

        $r->assertSee('طالب نشط')->assertDontSee('طالب منسحب')->assertDontSee('طالب مكتمل')->assertDontSee('طالب شعبة أخرى');
        $this->assertCount(1, $r->viewData('roster'));
    }

    /** @test */
    public function scores_are_saved_as_a_draft_and_every_change_is_logged()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);

        $this->save($exam, [$enrollment->id => 70.5])->assertSessionHasNoErrors();
        $grade = Grade::firstOrFail();
        $this->assertEquals(70.5, $grade->score);
        $this->assertSame('draft', $exam->fresh()->result_status);

        $this->save($exam, [$enrollment->id => 80])->assertSessionHasNoErrors();
        $this->assertEquals(80, $grade->fresh()->score);

        $changes = GradeChange::orderBy('id')->get();
        $this->assertCount(2, $changes);
        $this->assertSame(['entered', 'updated'], $changes->pluck('action')->all());
        $this->assertNull($changes[0]->old_score);
        $this->assertEquals(70.5, $changes[1]->old_score);
        $this->assertEquals(80, $changes[1]->new_score);
        $this->assertSame($this->facultyUser->id, $changes[1]->changed_by);
        $this->assertNotNull($changes[1]->created_at);
    }

    /** @test */
    public function saving_the_same_value_again_creates_no_noise_in_the_log()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);

        $this->save($exam, [$enrollment->id => 55]);
        $this->save($exam, [$enrollment->id => 55]);

        $this->assertSame(1, GradeChange::count());
    }

    /** @test */
    public function a_score_above_the_exam_maximum_or_negative_is_rejected()
    {
        $exam = $this->makeExam(null, ['max_score' => 50]);
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);

        $this->save($exam, [$enrollment->id => 50.01])->assertSessionHasErrors("scores.{$enrollment->id}");
        $this->save($exam, [$enrollment->id => 120])->assertSessionHasErrors("scores.{$enrollment->id}");
        $this->save($exam, [$enrollment->id => -1])->assertSessionHasErrors("scores.{$enrollment->id}");
        $this->save($exam, [$enrollment->id => 'abc'])->assertSessionHasErrors("scores.{$enrollment->id}");
        $this->assertSame(0, Grade::count());

        $this->save($exam, [$enrollment->id => 50])->assertSessionHasNoErrors();
        $this->assertEquals(50, Grade::first()->score);
    }

    /** @test */
    public function grades_cannot_be_recorded_for_students_not_actively_enrolled_in_the_section()
    {
        $exam = $this->makeExam();
        [, $enrolled] = $this->makeStudent();
        [, $dropped] = $this->makeStudent();
        [, $elsewhere] = $this->makeStudent();
        $ok = $this->enroll($enrolled);
        $droppedEnrollment = $this->enroll($dropped, null, 'dropped');
        $foreignEnrollment = $this->enroll($elsewhere, $this->makeSection($this->faculty));

        foreach ([$droppedEnrollment->id, $foreignEnrollment->id, 999999] as $bad) {
            $this->save($exam, [$ok->id => 60, $bad => 60])->assertSessionHasErrors('scores');
        }

        // الطلب المختلط لا يحفظ شيئًا حتى للمسجَّل الصحيح
        $this->assertSame(0, Grade::count());
    }

    /** @test */
    public function a_blank_field_never_erases_a_previous_grade()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $this->save($exam, [$enrollment->id => 90]);

        $this->save($exam, [$enrollment->id => ''])->assertSessionHasNoErrors();
        $this->save($exam, [$enrollment->id => null])->assertSessionHasNoErrors();

        $this->assertEquals(90, Grade::first()->score);
        $this->assertSame(1, GradeChange::count());
    }

    /** @test */
    public function the_database_prevents_duplicate_grades_for_one_student_and_exam()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $this->setGrade($exam, $enrollment, 10);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->setGrade($exam, $enrollment, 20);
    }

    /** @test */
    public function another_faculty_member_students_and_staff_cannot_enter_grades()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        [$otherUser] = $this->makeFaculty();
        [$studentUser] = $this->makeStudent();

        foreach ([$otherUser, $studentUser, $this->userWithRole('staff'), $this->userWithRole('student_affairs')] as $user) {
            $this->save($exam, [$enrollment->id => 70], $user)->assertForbidden();
        }
        $this->assertSame(0, Grade::count());
    }

    /** @test */
    public function grades_are_locked_once_submitted_even_for_the_owner_and_admin()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $this->setGrade($exam, $enrollment, 60);
        $exam->update(['result_status' => 'submitted']);

        $this->save($exam, [$enrollment->id => 99])->assertSessionHasErrors('exam');
        $this->assertEquals(60, Grade::first()->score);

        $admin = $this->userWithRole('admin', 'admin');
        $this->actingAs($admin)->put(route('exams.grades.update', $exam), ['scores' => [$enrollment->id => 99]])->assertSessionHasErrors('exam');
        $this->assertEquals(60, Grade::first()->score);
    }

    /** @test */
    public function the_section_owner_page_shows_the_change_history()
    {
        $exam = $this->makeExam();
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $this->save($exam, [$enrollment->id => 40]);
        $this->save($exam, [$enrollment->id => 45]);

        $this->actingAs($this->facultyUser)->get(route('exams.grades.edit', $exam))->assertOk()->assertSee('سجل التعديلات (2)');
    }
}
