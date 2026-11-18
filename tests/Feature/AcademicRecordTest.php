<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class AcademicRecordTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    private function published(array $attrs = [], ?\App\Models\Section $section = null): Exam
    {
        return $this->makeExam($section, $attrs + ['result_status' => 'published', 'published_at' => now()]);
    }

    private function record($user)
    {
        return $this->actingAs($user)->get(route('student.academic-record'))->assertOk();
    }

    /** @test */
    public function a_student_sees_only_their_own_published_results()
    {
        [$userA, $a] = $this->makeStudent('طالب أ');
        [, $b] = $this->makeStudent('طالب ب');
        $ea = $this->enroll($a);
        $eb = $this->enroll($b);
        $pub = $this->published(['name' => 'منشور']);
        $hidden = $this->makeExam(null, ['name' => 'غير منشور', 'result_status' => 'approved']);
        $this->setGrade($pub, $ea, 71);
        $this->setGrade($pub, $eb, 33);
        $this->setGrade($hidden, $ea, 99);

        $r = $this->actingAs($userA)->get(route('student.results.index'))->assertOk();

        $grades = $r->viewData('grades');
        $this->assertCount(1, $grades);
        $this->assertEquals(71, $grades->first()->score);
        $r->assertSee('منشور')->assertDontSee('غير منشور');
    }

    /** @test */
    public function results_in_every_unpublished_status_are_hidden_from_students()
    {
        [$user, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);

        foreach (['draft', 'submitted', 'approved', 'rejected'] as $i => $status) {
            $exam = $this->makeExam(null, ['name' => "E$i", 'result_status' => $status]);
            $this->setGrade($exam, $enrollment, 50 + $i);
        }

        $this->assertCount(0, $this->actingAs($user)->get(route('student.results.index'))->viewData('grades'));
        $record = $this->record($user)->viewData('record');
        $this->assertSame([], $record['terms'][0]['courses'][0]['exams']);
        $this->assertFalse($record['terms'][0]['courses'][0]['complete']);
    }

    /** @test */
    public function the_academic_record_calculates_totals_without_assuming_any_policy()
    {
        [$user, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $mid = $this->published(['name' => 'نصفي', 'max_score' => 50, 'weight' => 40]);
        $final = $this->published(['name' => 'نهائي', 'type' => 'final', 'max_score' => 100, 'weight' => 60]);
        $this->setGrade($mid, $enrollment, 40);   // 40/50*40 = 32
        $this->setGrade($final, $enrollment, 75); // 75/100*60 = 45

        $course = $this->record($user)->viewData('record')['terms'][0]['courses'][0];

        $this->assertEquals(77, $course['total']);
        $this->assertEquals(77, $course['percentage']);
        $this->assertTrue($course['complete']);
        // لا سلم ولا نسبة نجاح مضبوطان ← لا تقدير ولا نجاح/رسوب ولا معدل
        $this->assertNull($course['letter']);
        $this->assertNull($course['passed']);
        $this->assertSame('نتيجة مكتملة', $course['status_label']);
        $record = $this->record($user)->viewData('record');
        $this->assertNull($record['cumulative_gpa']);
        $this->assertFalse($record['has_scale']);
    }

    /** @test */
    public function a_course_with_an_unpublished_exam_is_incomplete_and_shows_no_percentage()
    {
        [$user, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $mid = $this->published(['name' => 'نصفي', 'max_score' => 50, 'weight' => 40]);
        $final = $this->makeExam(null, ['name' => 'نهائي', 'weight' => 60, 'result_status' => 'approved']);
        $this->setGrade($mid, $enrollment, 40);
        $this->setGrade($final, $enrollment, 90);

        $course = $this->record($user)->viewData('record')['terms'][0]['courses'][0];

        $this->assertFalse($course['complete']);
        $this->assertNull($course['percentage']);
        $this->assertCount(1, $course['exams']);       // المنشور فقط
        $this->assertEquals(32, $course['total']);
        $this->assertSame('نتيجة غير مكتملة', $course['status_label']);
    }

    /** @test */
    public function letters_pass_fail_and_gpa_appear_only_when_configured()
    {
        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, 50);
        AcademicSetting::put(AcademicSetting::GRADE_SCALE, [
            ['letter' => 'A', 'min' => 85, 'points' => 4],
            ['letter' => 'B', 'min' => 70, 'points' => 3],
            ['letter' => 'F', 'min' => 0, 'points' => 0],
        ]);
        [$user, $student] = $this->makeStudent();
        $passCourse = $this->makeCourse('P1', 3);
        $failCourse = $this->makeCourse('F1', 1);
        $s1 = $this->makeSection($this->faculty, $passCourse);
        $s2 = $this->makeSection($this->faculty, $failCourse);
        $e1 = $this->enroll($student, $s1);
        $e2 = $this->enroll($student, $s2);
        $x1 = $this->published(['name' => 'ن', 'max_score' => 100, 'weight' => 100], $s1);
        $x2 = $this->published(['name' => 'ن', 'max_score' => 100, 'weight' => 100], $s2);
        $this->setGrade($x1, $e1, 72);  // B (3 pts × 3h)
        $this->setGrade($x2, $e2, 30);  // F (0 pts × 1h)

        $record = $this->record($user)->viewData('record');
        $courses = collect($record['terms'][0]['courses'])->keyBy(fn ($c) => $c['course']->code);

        $this->assertSame('B', $courses['P1']['letter']);
        $this->assertTrue($courses['P1']['passed']);
        $this->assertSame('ناجح', $courses['P1']['status_label']);
        $this->assertSame('F', $courses['F1']['letter']);
        $this->assertFalse($courses['F1']['passed']);
        $this->assertSame('راسب', $courses['F1']['status_label']);
        // المعدل = (3×3 + 0×1) ÷ 4 = 2.25
        $this->assertEquals(2.25, $record['terms'][0]['gpa']);
        $this->assertEquals(2.25, $record['cumulative_gpa']);
    }

    /** @test */
    public function dropped_enrollments_without_published_results_are_hidden_and_other_students_data_never_leaks()
    {
        [$user, $student] = $this->makeStudent();
        [, $other] = $this->makeStudent('زميل');
        $dropped = $this->enroll($student, $this->makeSection($this->faculty, $this->makeCourse('D1')), 'dropped');
        $mine = $this->enroll($student);
        $theirs = $this->enroll($other);
        $exam = $this->published();
        $this->setGrade($exam, $mine, 60);
        $this->setGrade($exam, $theirs, 11);

        $record = $this->record($user)->viewData('record');

        $codes = collect($record['terms'][0]['courses'])->pluck('course.code')->all();
        $this->assertSame(['C1'], $codes);
        $this->assertEquals(24, $record['terms'][0]['courses'][0]['total']); // 60/100*40
        $this->assertSame($student->id, $record['student']->id);
        $this->record($user)->assertDontSee('زميل');
    }

    /** @test */
    public function terms_are_grouped_and_ordered_chronologically()
    {
        [$user, $student] = $this->makeStudent();
        $second = \App\Models\AcademicTerm::create(['academic_year_id' => $this->term->academic_year_id, 'semester' => 'second']);
        $secondSection = \App\Models\Section::create(['course_id' => $this->makeCourse('C2')->id, 'academic_term_id' => $second->id, 'faculty_profile_id' => $this->faculty->id, 'code' => 'Z', 'capacity' => 5]);
        $this->enroll($student, $secondSection);
        $this->enroll($student);

        $terms = $this->record($user)->viewData('record')['terms'];

        $this->assertCount(2, $terms);
        $this->assertSame($this->term->id, $terms[0]['term']->id);
        $this->assertSame($second->id, $terms[1]['term']->id);
    }

    /** @test */
    public function the_transcript_page_shows_institution_student_and_issue_date_with_published_data_only()
    {
        [$user, $student] = $this->makeStudent('الطالب المثالي');
        $enrollment = $this->enroll($student);
        $this->setGrade($this->published(['max_score' => 100, 'weight' => 100]), $enrollment, 88);
        $hidden = $this->makeExam(null, ['result_status' => 'submitted', 'weight' => 0]);
        $this->setGrade($hidden, $enrollment, 12);

        $this->actingAs($user)->get(route('student.transcript'))->assertOk()
            ->assertSee(config('academic.institution_name'))
            ->assertSee('الطالب المثالي')->assertSee($student->number_student)
            ->assertSee(now()->format('Y-m-d'))->assertSee('كشف الدرجات')->assertSee('تصدير PDF');
    }

    /** @test */
    public function the_transcript_can_be_exported_as_a_real_pdf()
    {
        [$user, $student] = $this->makeStudent('الطالب المثالي');
        $enrollment = $this->enroll($student);
        $this->setGrade($this->published(['max_score' => 100, 'weight' => 100]), $enrollment, 88);

        $response = $this->actingAs($user)->get(route('student.transcript.pdf'))->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertGreaterThan(2000, strlen($response->getContent()));
    }

    /** @test */
    public function a_user_without_a_student_profile_gets_empty_pages_not_errors()
    {
        $user = $this->userWithRole('student', 'student');

        $this->actingAs($user)->get(route('student.results.index'))->assertOk();
        $this->actingAs($user)->get(route('student.academic-record'))->assertOk()->assertSee('لا يوجد ملف أكاديمي');
        $this->actingAs($user)->get(route('student.transcript'))->assertOk();
        $this->actingAs($user)->get(route('student.transcript.pdf'))->assertNotFound();
    }

    /** @test */
    public function only_students_can_open_the_student_result_pages()
    {
        foreach ([$this->facultyUser, $this->userWithRole('staff'), $this->userWithRole('student_affairs'), $this->userWithRole('admin', 'admin')] as $user) {
            foreach (['student.results.index', 'student.academic-record', 'student.transcript', 'student.transcript.pdf'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }

        auth()->logout();
        $this->get(route('student.results.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function staff_can_view_a_student_record_only_with_the_explicit_permission()
    {
        [, $student] = $this->makeStudent('طالب للعرض');
        $enrollment = $this->enroll($student);
        $this->setGrade($this->published(), $enrollment, 70);
        $url = route('staff.academic-record', $student);

        foreach ([$this->facultyUser, $this->userWithRole('staff'), $this->userWithRole('student_affairs'), $this->makeStudent()[0]] as $user) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }

        $viewer = $this->userWithPermissions(['academic-records.viewAll']);
        $this->actingAs($viewer)->get($url)->assertOk()->assertSee('طالب للعرض');
        $this->actingAs($this->userWithRole('admin', 'admin'))->get($url)->assertOk();
    }

    /** @test */
    public function academic_settings_are_protected_validated_and_saved()
    {
        $manager = $this->userWithPermissions(['academic-settings.manage']);
        $payload = ['pass_percentage' => 60, 'scale' => [
            ['letter' => 'A', 'min' => 80, 'points' => 4], ['letter' => 'B', 'min' => 0, 'points' => 2], ['letter' => '', 'min' => '', 'points' => ''],
        ]];

        // بلا صلاحية
        foreach ([$this->facultyUser, $this->userWithRole('staff'), $this->userWithRole('student_affairs')] as $user) {
            $this->actingAs($user)->get(route('academic-settings.edit'))->assertForbidden();
            $this->actingAs($user)->put(route('academic-settings.update'), $payload)->assertForbidden();
        }
        $this->assertNull(AcademicSetting::passPercentage());

        // تحقق: تكرار الحد، سلم لا يبدأ من 0، نسبة خارج النطاق
        $this->actingAs($manager)->put(route('academic-settings.update'), ['scale' => [['letter' => 'A', 'min' => 50], ['letter' => 'B', 'min' => 50]]])->assertSessionHasErrors('scale');
        $this->actingAs($manager)->put(route('academic-settings.update'), ['scale' => [['letter' => 'A', 'min' => 50]]])->assertSessionHasErrors('scale');
        $this->actingAs($manager)->put(route('academic-settings.update'), ['pass_percentage' => 150])->assertSessionHasErrors('pass_percentage');

        $this->actingAs($manager)->put(route('academic-settings.update'), $payload)->assertSessionHasNoErrors();
        $this->assertSame(60.0, AcademicSetting::passPercentage());
        $this->assertSame(['A', 'B'], array_column(AcademicSetting::gradeScale(), 'letter'));
        $this->actingAs($manager)->get(route('academic-settings.edit'))->assertOk();
    }
}
