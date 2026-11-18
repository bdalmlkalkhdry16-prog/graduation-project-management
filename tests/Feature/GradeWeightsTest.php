<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Services\ExamResultWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * Phase 7 v2 — أوزان مرنة: لا يُشترط أن يساوي مجموعها 100.
 * النسبة = Σ(درجة ÷ النهائية × الوزن) ÷ Σ الأوزان × 100.
 */
class GradeWeightsTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    /** ينشئ اختبارات منشورة بأوزان/درجات محددة لطالب واحد ويعيد صف المقرر من السجل الأكاديمي. */
    private function courseRow(array $weights, array $scores): array
    {
        [$user, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);

        foreach ($weights as $i => $weight) {
            $exam = $this->makeExam(null, [
                'name' => "اختبار $i", 'max_score' => 100, 'weight' => $weight,
                'result_status' => 'published', 'published_at' => now(),
            ]);
            $this->setGrade($exam, $enrollment, $scores[$i]);
        }

        return $this->actingAs($user)->get(route('student.academic-record'))->assertOk()->viewData('record')['terms'][0]['courses'][0];
    }

    /** @test */
    public function weights_summing_to_70_are_normalized_by_their_sum()
    {
        // (80/100×40 + 60/100×30) ÷ 70 × 100 = 50 ÷ 70 × 100
        $course = $this->courseRow([40, 30], [80, 60]);

        $this->assertTrue($course['complete']);
        $this->assertEquals(71.43, $course['percentage']);
        $this->assertFalse($course['weights_zero']);
    }

    /** @test */
    public function weights_summing_to_90_are_normalized_by_their_sum()
    {
        // (32 + 30) ÷ 90 × 100
        $course = $this->courseRow([40, 50], [80, 60]);

        $this->assertEquals(68.89, $course['percentage']);
    }

    /** @test */
    public function weights_summing_to_120_are_normalized_by_their_sum()
    {
        // (40 + 42) ÷ 120 × 100
        $course = $this->courseRow([50, 70], [80, 60]);

        $this->assertEquals(68.33, $course['percentage']);
    }

    /** @test */
    public function weights_summing_to_exactly_100_give_the_same_result_as_before()
    {
        $course = $this->courseRow([40, 60], [80, 60]);

        $this->assertEquals(68.0, $course['percentage']);
    }

    /** @test */
    public function a_zero_weight_on_a_single_exam_is_allowed_and_contributes_nothing()
    {
        // الاختبار الأول وزنه صفر: النتيجة تعتمد على الثاني وحده
        $course = $this->courseRow([0, 50], [10, 80]);

        $this->assertTrue($course['complete']);
        $this->assertEquals(80.0, $course['percentage']);
    }

    /** @test */
    public function a_zero_weight_exam_can_be_created_by_the_doctor()
    {
        $payload = $this->validExamPayload(['weight' => 0]);

        $this->actingAs($this->facultyUser)->post(route('exams.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('exams', ['name' => 'نصفي', 'weight' => 0]);
    }

    /** @test */
    public function weights_are_not_required_to_sum_to_100_when_creating_exams()
    {
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'أ', 'weight' => 70]))->assertSessionHasNoErrors();
        $this->actingAs($this->facultyUser)->post(route('exams.store'), $this->validExamPayload(['name' => 'ب', 'weight' => 120, 'start_time' => '13:00']))->assertSessionHasNoErrors();

        $this->assertEquals(190, Exam::where('section_id', $this->section->id)->sum('weight'));
    }

    /** @test */
    public function when_the_sum_of_weights_is_zero_the_result_is_blocked_at_submission()
    {
        [, $student] = $this->makeStudent();
        $enrollment = $this->enroll($student);
        $exam = $this->makeExam(null, ['weight' => 0]);
        $this->makeExam(null, ['weight' => 0]);
        $this->setGrade($exam, $enrollment, 50);

        try {
            app(ExamResultWorkflow::class)->submit($exam, $this->facultyUser);
            $this->fail('كان متوقعًا رفض التقديم عند مجموع أوزان صفري');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('exam', $e->errors());
        }

        $this->assertSame('draft', $exam->fresh()->result_status);
        $this->assertSame(0, $exam->rosterEntries()->count());
    }

    /** @test */
    public function when_the_sum_of_weights_is_zero_no_percentage_is_calculated_in_the_record()
    {
        $course = $this->courseRow([0, 0], [50, 70]);

        $this->assertTrue($course['weights_zero']);
        $this->assertNull($course['percentage']);
        $this->assertNull($course['letter']);
        $this->assertNull($course['passed']);
    }
}
