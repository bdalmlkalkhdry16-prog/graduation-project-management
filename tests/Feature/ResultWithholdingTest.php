<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\FeeSchedule;
use App\Models\StudentServiceRequest;
use App\Services\AcademicRecordService;
use App\Services\FeeService;
use App\Services\FeeStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class ResultWithholdingTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $accountant;
    private $studentUser;
    private $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, 50);
        $this->accountant = $this->userWithRole('accountant');
        [$this->studentUser, $this->profile] = $this->makeStudent();
    }

    private function publishedResult($course, $term = null, float $percent = 80): void
    {
        $section = $this->makeSection($this->faculty, $course);
        if ($term) {
            $section->update(['academic_term_id' => $term->id]);
        }
        $enrollment = $this->enroll($this->profile, $section);
        $exam = $this->makeExam($section->fresh(), ['max_score' => 100, 'weight' => 100, 'result_status' => 'published', 'published_at' => now()]);
        $this->setGrade($exam, $enrollment, $percent);
    }

    private function feeFor($year, $amount = 100)
    {
        FeeSchedule::firstOrCreate(
            ['department_id' => $this->spec->department_id, 'academic_year_id' => $year->id],
            ['amount' => $amount, 'currency' => 'USD']
        );

        return app(FeeService::class)->generateFor($this->profile, $year, $this->accountant);
    }

    private function pay($fee, $amount): void
    {
        app(FeeService::class)->recordPayment($fee, $amount, now()->toDateString(), null, $this->accountant);
    }

    private function grades()
    {
        return $this->actingAs($this->studentUser)->get(route('student.results.index'))->assertOk()->viewData('grades');
    }

    /** @test */
    public function a_year_without_a_generated_fee_is_never_blocked()
    {
        $this->publishedResult($this->course);
        $this->assertCount(1, $this->grades());
        $this->assertTrue(app(FeeStatus::class)->withheldYears($this->profile)->isEmpty());
    }

    /** @test */
    public function an_unpaid_year_hides_results_and_the_record_for_the_student()
    {
        $this->publishedResult($this->course);
        $this->feeFor($this->term->academicYear);

        $this->assertCount(0, $this->grades());
        $this->actingAs($this->studentUser)->get(route('student.results.index'))->assertSee('تم حجب نتائج');
        $this->actingAs($this->studentUser)->get(route('student.academic-record'))->assertOk()->assertSee('تم حجب نتائج');
        $this->actingAs($this->studentUser)->get(route('student.transcript'))->assertOk()->assertSee('تم حجب نتائج');
        $this->actingAs($this->studentUser)->get(route('student.transcript.pdf'))->assertForbidden();
    }

    /** @test */
    public function a_partial_payment_still_blocks_and_full_settlement_restores_the_view()
    {
        $this->publishedResult($this->course);
        $fee = $this->feeFor($this->term->academicYear, 100);

        $this->pay($fee, 40);
        $this->assertCount(0, $this->grades());

        $this->pay($fee->fresh(), 60);
        $this->assertCount(1, $this->grades());
        $this->actingAs($this->studentUser)->get(route('student.transcript.pdf'))->assertOk();
    }

    /** @test */
    public function only_the_unpaid_year_is_hidden_and_its_terms_do_not_leak_into_the_gpa()
    {
        $year1 = $this->term->academicYear;
        $year2 = AcademicYear::create(['name' => '2026-2027', 'year' => 2027]);
        $term2 = AcademicTerm::create(['academic_year_id' => $year2->id, 'semester' => 'first']);

        $this->publishedResult($this->course, $this->term, 90);
        $this->publishedResult($this->makeCourse('C2'), $term2, 60);

        $this->pay($this->feeFor($year1), 100);
        $this->feeFor($year2);

        $this->assertCount(1, $this->grades());

        $full = app(AcademicRecordService::class)->forStudent($this->profile);
        $hidden = app(AcademicRecordService::class)->forStudent($this->profile, withholdUnpaid: true);
        $this->assertCount(2, $full['terms']);
        $this->assertCount(1, $hidden['terms']);
        $this->assertSame([$year2->id], $hidden['withheld_years']->pluck('id')->all());
    }

    /** @test */
    public function staff_views_and_the_service_in_default_mode_are_not_affected()
    {
        $this->publishedResult($this->course);
        $this->feeFor($this->term->academicYear);

        $record = app(AcademicRecordService::class)->forStudent($this->profile);
        $this->assertCount(1, $record['terms']);
        $this->assertTrue($record['withheld_years']->isEmpty());
    }

    /** @test */
    public function the_official_transcript_pdf_is_withheld_from_the_student_but_not_from_the_officer()
    {
        $this->publishedResult($this->course);
        $this->feeFor($this->term->academicYear);
        $officer = $this->userWithRole('eservices_officer');
        $r = StudentServiceRequest::create(['student_profile_id' => $this->profile->id, 'type' => 'transcript', 'status' => 'approved']);

        $this->actingAs($this->studentUser)->get(route('student.service-requests.transcript', $r))->assertForbidden();
        $this->actingAs($officer)->get(route('staff.service-requests.transcript', $r))->assertOk();
    }
}
