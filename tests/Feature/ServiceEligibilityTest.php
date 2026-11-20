<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\StudentServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * E4 — شروط شهادة القيد وإفادة التخرج ومهلة التظلم.
 */
class ServiceEligibilityTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $studentUser;
    private $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, 50);
        [$this->studentUser, $this->profile] = $this->makeStudent();
    }

    private function submit(string $type, array $extra = [])
    {
        // مستخدم جديد حتى لا تبقى علاقة studentProfile المخزَّنة من طلب سابق
        return $this->actingAs($this->studentUser->fresh())->post(route('student.service-requests.store'), ['type' => $type] + $extra);
    }

    private function passedCourse(float $percent = 80)
    {
        $section = $this->makeSection($this->faculty, $this->course);
        $enrollment = $this->enroll($this->profile, $section);
        $exam = $this->makeExam($section, ['max_score' => 100, 'weight' => 100, 'result_status' => 'published', 'published_at' => now()]);
        $this->setGrade($exam, $enrollment, $percent);

        return [$enrollment, $exam];
    }

    /** @test */
    public function an_enrollment_certificate_is_issued_only_to_a_registered_student_with_program_and_level()
    {
        $this->submit('enrollment_certificate')->assertSessionHasNoErrors();

        foreach (['suspended', 'withdrawn', 'graduated'] as $status) {
            StudentServiceRequest::query()->delete();
            $this->profile->forceFill(['academic_status' => $status])->saveQuietly();
            $this->submit('enrollment_certificate')->assertSessionHasErrors('type');
        }

        $this->profile->forceFill(['academic_status' => 'active', 'current_level_id' => null])->saveQuietly();
        StudentServiceRequest::query()->delete();
        $this->submit('enrollment_certificate')->assertSessionHasErrors('type');
    }

    /** @test */
    public function a_graduation_statement_requires_a_completed_study_plan()
    {
        $this->submit('graduation_statement')->assertSessionHasErrors('type');   // لا نتائج

        $this->passedCourse(30);
        $this->submit('graduation_statement')->assertSessionHasErrors('type');   // راسب
        $this->assertSame(0, StudentServiceRequest::count());
    }

    /** @test */
    public function a_graduation_statement_is_allowed_after_completion_even_before_the_degree_is_issued()
    {
        $this->passedCourse(75);

        $this->assertSame('active', $this->profile->fresh()->academic_status);   // لم يُسجَّل كخريج بعد
        $this->submit('graduation_statement')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_service_requests', ['type' => 'graduation_statement', 'student_profile_id' => $this->profile->id]);
    }

    /** @test */
    public function there_is_no_appeal_deadline_unless_the_institution_sets_one()
    {
        [$enrollment, $exam] = $this->passedCourse();
        $exam->update(['published_at' => now()->subYears(2)]);

        $this->assertNull(AcademicSetting::appealWindowDays());
        $this->submit('grade_appeal', ['enrollment_id' => $enrollment->id, 'description' => 'سبب التظلم مفصل'])->assertSessionHasNoErrors();
    }

    /** @test */
    public function the_appeal_deadline_when_configured_is_counted_from_publication()
    {
        AcademicSetting::put(AcademicSetting::APPEAL_WINDOW_DAYS, 7);
        [$enrollment, $exam] = $this->passedCourse();
        $payload = ['enrollment_id' => $enrollment->id, 'description' => 'سبب التظلم مفصل'];

        $exam->update(['published_at' => now()->subDays(6)]);
        $this->submit('grade_appeal', $payload)->assertSessionHasNoErrors();

        StudentServiceRequest::query()->delete();
        $exam->update(['published_at' => now()->subDays(8)]);
        $this->submit('grade_appeal', $payload)->assertSessionHasErrors('enrollment_id');
        $this->assertSame(0, StudentServiceRequest::count());
    }

    /** @test */
    public function the_deadline_is_managed_from_the_academic_settings_and_can_be_cleared()
    {
        $manager = $this->userWithPermissions(['academic-settings.manage']);

        $this->actingAs($manager)->put(route('academic-settings.update'), ['appeal_window_days' => 14])->assertSessionHasNoErrors();
        $this->assertSame(14, AcademicSetting::appealWindowDays());

        $this->actingAs($manager)->put(route('academic-settings.update'), ['appeal_window_days' => 0])->assertSessionHasErrors('appeal_window_days');
        $this->assertSame(14, AcademicSetting::appealWindowDays());

        $this->actingAs($manager)->put(route('academic-settings.update'), [])->assertSessionHasNoErrors();
        $this->assertNull(AcademicSetting::appealWindowDays());
    }
}
