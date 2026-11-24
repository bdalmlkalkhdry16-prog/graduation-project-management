<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\StudentServiceRequest;
use App\Services\AcademicStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class AcademicStatsTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private function publishedSectionWith(array $percents)
    {
        $exam = $this->makeExam($this->section, ['max_score' => 100, 'weight' => 100]);
        foreach ($percents as $p) {
            [, $profile] = $this->makeStudent();
            $this->setGrade($exam, $this->enroll($profile), $p);
        }
        $this->setStatus($exam, 'published');

        return $exam;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    /** @test */
    public function the_pass_rate_counts_only_complete_results_and_excludes_dropped_students()
    {
        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, 50);
        $this->publishedSectionWith([80, 60, 40, 30]);              // 2 ناجح، 2 راسب
        [, $dropped] = $this->makeStudent();
        $this->enroll($dropped, $this->section, 'dropped');          // منسحب مستبعد

        $row = app(AcademicStatsService::class)->coursePassRates($this->term->id)->first();
        $this->assertSame(4, $row['enrolled']);
        $this->assertSame(4, $row['complete']);
        $this->assertSame(2, $row['passed']);
        $this->assertSame(2, $row['failed']);
        $this->assertSame(50.0, $row['pass_rate']);
    }

    /** @test */
    public function unpublished_sections_are_incomplete_and_have_no_rate()
    {
        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, 50);
        $exam = $this->makeExam($this->section);
        [, $p] = $this->makeStudent();
        $this->setGrade($exam, $this->enroll($p), 90);

        $row = app(AcademicStatsService::class)->coursePassRates($this->term->id)->first();
        $this->assertSame(1, $row['incomplete']);
        $this->assertNull($row['pass_rate']);
    }

    /** @test */
    public function without_a_configured_pass_percentage_no_rate_is_invented()
    {
        $this->publishedSectionWith([80, 20]);
        $row = app(AcademicStatsService::class)->coursePassRates($this->term->id)->first();
        $this->assertNull($row['pass_rate']);
        $this->actingAs($this->userWithRole('admin', 'admin'))->get(route('academic-stats'))->assertOk()->assertSee('غير محددة')->assertSee('لم تُضبط نسبة النجاح');
    }

    /** @test */
    public function the_overview_counts_are_correct()
    {
        $exam = $this->makeExam();
        $this->makeExam($this->section, ['name' => 'مراجعة'])->update(['result_status' => 'submitted']);
        $this->makeExam($this->section, ['name' => 'نشر'])->update(['result_status' => 'approved']);
        [, $p] = $this->makeStudent();
        $this->enroll($p);
        StudentServiceRequest::create(['student_profile_id' => $p->id, 'type' => 're_enrollment', 'status' => 'pending']);
        StudentServiceRequest::create(['student_profile_id' => $p->id, 'type' => 're_enrollment', 'status' => 'completed']);

        $o = app(AcademicStatsService::class)->overview();
        $this->assertSame(1, $o['students']);
        $this->assertSame(1, $o['active_enrollments']);
        $this->assertSame(1, $o['exams_to_review']);
        $this->assertSame(1, $o['exams_to_publish']);
        $this->assertSame(1, $o['open_requests']);
        $this->assertNull($o['pass_percentage']);
    }

    /** @test */
    public function the_page_and_dashboard_are_for_admin_and_control_only()
    {
        $this->actingAs($this->userWithRole('admin', 'admin'))->get(route('academic-stats'))->assertOk()->assertSee('إحصاءات أكاديمية');
        $this->actingAs($this->userWithRole('admin', 'admin'))->get(route('dashboard'))->assertOk()->assertSee('الشؤون الأكاديمية والمالية');
        $this->actingAs($this->userWithRole('control'))->get(route('academic-stats'))->assertOk();

        foreach ([$this->makeStudent()[0], $this->userWithRole('accountant'), $this->facultyUser, $this->userWithRole('eservices_officer')] as $u) {
            $this->actingAs($u)->get(route('academic-stats'))->assertForbidden();
        }
    }

    /** @test */
    public function an_empty_system_renders_without_errors()
    {
        \App\Models\AcademicTerm::query()->delete();
        $this->actingAs($this->userWithRole('admin', 'admin'))->get(route('academic-stats'))->assertOk();
    }
}
