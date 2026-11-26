<?php

namespace Tests\Feature;

use App\Models\AcademicSetting as S;
use App\Models\ActivityLog;
use App\Models\FeeSchedule;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Services\FeeService;
use App\Services\ProjectEligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class ProjectAcademicLinkTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private Project $project;
    private User $supervisor;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->admin = $this->userWithRole('admin', 'admin');
        $this->project = Project::factory()->create(['supervisor_id' => $this->supervisor->id, 'status' => 'draft']);
    }

    private function add(User $by, User $student, array $extra = [])
    {
        return $this->actingAs($by)->post(route('projects.members.store', $this->project), ['student_id' => $student->id, 'role' => 'member'] + $extra);
    }

    private function passCourse($profile, string $code, int $hours = 3, float $percent = 80): void
    {
        $section = $this->makeSection($this->faculty, $this->makeCourse($code, $hours));
        $enrollment = $this->enroll($profile, $section);
        $exam = $this->makeExam($section, ['max_score' => 100, 'weight' => 100, 'result_status' => 'published', 'published_at' => now()]);
        $this->setGrade($exam, $enrollment, $percent);
    }

    /** @test */
    public function without_any_setting_nothing_changes_for_old_style_students()
    {
        $oldStudent = User::factory()->student()->create(); // بلا ملف أكاديمي
        $this->assertSame([], app(ProjectEligibility::class)->reasons($oldStudent, $this->project));
        $this->add($this->supervisor, $oldStudent)->assertSessionHas('success');
        $this->assertSame(1, $this->project->members()->count());
    }

    /** @test */
    public function the_max_members_setting_blocks_extra_members()
    {
        S::put(S::PROJECT_MAX_MEMBERS, 1);
        $a = User::factory()->student()->create();
        $b = User::factory()->student()->create();
        $this->add($this->supervisor, $a)->assertSessionHas('success');
        $this->add($this->supervisor, $b)->assertSessionHas('error');
        $this->assertSame(1, $this->project->members()->count());
    }

    /** @test */
    public function the_single_active_project_setting_blocks_a_second_live_project_but_not_finished_ones()
    {
        S::put(S::PROJECT_SINGLE_ACTIVE, true);
        $student = User::factory()->student()->create();
        $other = Project::factory()->create(['supervisor_id' => $this->supervisor->id, 'status' => 'approved']);
        ProjectMember::create(['project_id' => $other->id, 'student_id' => $student->id, 'role' => 'leader', 'joined_at' => now()]);

        $this->add($this->supervisor, $student)->assertSessionHas('error');

        $other->update(['status' => 'completed']);
        $this->add($this->supervisor, $student)->assertSessionHas('success');
    }

    /** @test */
    public function the_completed_hours_rule_counts_each_passed_course_once_and_needs_a_pass_percentage()
    {
        [$user, $profile] = $this->makeStudent();
        S::put(S::PROJECT_MIN_COMPLETED_HOURS, 6);

        $this->assertStringContainsString('نسبة النجاح', implode(' ', app(ProjectEligibility::class)->reasons($user, $this->project)));

        S::put(S::PASS_PERCENTAGE, 50);
        $this->passCourse($profile, 'P1', 3);
        $this->assertStringContainsString('أقل من المطلوب', implode(' ', app(ProjectEligibility::class)->reasons($user->fresh(), $this->project)));
        $this->add($this->supervisor, $user)->assertSessionHas('error');

        $this->passCourse($profile, 'P2', 3, 40); // راسب: لا يُحتسب
        $this->assertNotSame([], app(ProjectEligibility::class)->reasons($user->fresh(), $this->project));

        $this->passCourse($profile, 'P3', 3);
        $this->assertSame(6.0, app(\App\Services\AcademicRecordService::class)->completedHours($profile->fresh()));
        $this->add($this->supervisor, $user->fresh())->assertSessionHas('success');
    }

    /** @test */
    public function the_min_level_rule_uses_the_current_level_number()
    {
        [$user, $profile] = $this->makeStudent();
        S::put(S::PROJECT_MIN_LEVEL, 2);
        $this->assertNotSame([], app(ProjectEligibility::class)->reasons($user, $this->project));

        $level2 = \App\Models\Level::create(['program_id' => $this->program->id, 'level_number' => 2, 'name' => 'المستوى الثاني']);
        $profile->update(['current_level_id' => $level2->id]);
        $this->assertSame([], app(ProjectEligibility::class)->reasons($user->fresh(), $this->project));
    }

    /** @test */
    public function the_fees_rule_blocks_unsettled_years_and_an_admin_release_lifts_it()
    {
        [$user, $profile] = $this->makeStudent();
        S::put(S::PROJECT_REQUIRE_FEES_SETTLED, true);
        $this->assertSame([], app(ProjectEligibility::class)->reasons($user, $this->project), 'لا رسم مولَّد = لا حجب');

        $year = $this->term->academicYear;
        FeeSchedule::create(['department_id' => $this->spec->department_id, 'academic_year_id' => $year->id, 'amount' => 100, 'currency' => 'USD']);
        $fee = app(FeeService::class)->generateFor($profile, $year, $this->userWithRole('accountant'));
        $this->assertStringContainsString('رسوم غير مسددة', implode(' ', app(ProjectEligibility::class)->reasons($user->fresh(), $this->project)));

        app(FeeService::class)->setResultsRelease($fee, true, 'استثناء إداري', $this->admin);
        $this->assertSame([], app(ProjectEligibility::class)->reasons($user->fresh(), $this->project));
    }

    /** @test */
    public function a_student_without_an_academic_profile_is_blocked_only_when_an_academic_rule_is_set()
    {
        $old = User::factory()->student()->create();
        S::put(S::PROJECT_MIN_LEVEL, 1);
        $this->assertStringContainsString('لا يوجد ملف أكاديمي', implode(' ', app(ProjectEligibility::class)->reasons($old, $this->project)));
    }

    /** @test */
    public function only_the_admin_can_override_and_it_needs_a_reason_and_is_logged()
    {
        S::put(S::PROJECT_MAX_MEMBERS, 1);
        $a = User::factory()->student()->create();
        $b = User::factory()->student()->create();
        $this->add($this->supervisor, $a);

        $this->add($this->supervisor, $b, ['override_reason' => 'سبب كافٍ جدًا'])->assertSessionHas('error');
        $this->add($this->admin, $b)->assertSessionHas('error');
        $this->add($this->admin, $b, ['override_reason' => 'abc'])->assertSessionHas('error');
        $this->assertSame(1, $this->project->members()->count());

        $this->add($this->admin, $b, ['override_reason' => 'قرار لجنة المشاريع'])->assertSessionHas('success');
        $this->assertSame(2, $this->project->members()->count());
        $this->assertTrue(ActivityLog::where('action', 'project_member.eligibility_override')->exists());
    }

    /** @test */
    public function invitations_are_checked_when_sent_and_again_when_accepted()
    {
        S::put(S::PROJECT_MIN_LEVEL, 2);
        [$user, $profile] = $this->makeStudent();
        $user->update(['email' => 'inv@example.com']);

        $this->actingAs($this->supervisor)->post(route('projects.members.send-invite', $this->project), ['student_email' => 'inv@example.com'])->assertSessionHas('error');
        $this->assertSame(0, Notification::where('user_id', $user->id)->count());

        // دعوة صالحة قديمة ثم تغيّرت الأهلية قبل القبول
        S::put(S::PROJECT_MIN_LEVEL, null);
        $this->actingAs($this->supervisor)->post(route('projects.members.send-invite', $this->project), ['student_email' => 'inv@example.com'])->assertSessionHas('success');
        $link = Notification::where('user_id', $user->id)->latest('id')->value('link');
        S::put(S::PROJECT_MIN_LEVEL, 2);
        $this->actingAs($user)->get($link)->assertSessionHas('error');
        $this->assertSame(0, $this->project->members()->count());

        S::put(S::PROJECT_MIN_LEVEL, null);
        $this->actingAs($user)->get($link)->assertSessionHas('success');
        $this->assertSame(1, $this->project->members()->count());
    }

    /** @test */
    public function a_completed_or_rejected_project_accepts_no_new_members_by_invite()
    {
        $user = User::factory()->student()->create(['email' => 'late@example.com']);
        $this->actingAs($this->supervisor)->post(route('projects.members.send-invite', $this->project), ['student_email' => 'late@example.com'])->assertSessionHas('success');
        $link = Notification::where('user_id', $user->id)->latest('id')->value('link');

        $this->project->update(['status' => 'completed']);
        $this->actingAs($user)->get($link)->assertSessionHas('error');
        $this->assertSame(0, $this->project->members()->count());
    }

    /** @test */
    public function the_graduation_statement_can_require_a_completed_project()
    {
        [$user, $profile] = $this->makeStudent();
        $svc = app(ProjectEligibility::class);

        $this->assertNull($svc->graduationBlocker($profile), 'غير مشروط ما لم يُفعَّل');

        S::put(S::GRADUATION_REQUIRES_PROJECT, true);
        $this->assertNotNull($svc->graduationBlocker($profile));

        $member = ProjectMember::create(['project_id' => $this->project->id, 'student_id' => $user->id, 'role' => 'leader', 'joined_at' => now()]);
        $this->assertNotNull($svc->graduationBlocker($profile), 'مشروع غير مكتمل لا يكفي');

        $this->project->update(['status' => 'completed']);
        $this->assertNull($svc->graduationBlocker($profile));
    }

    /** @test */
    public function the_settings_page_saves_and_shows_the_project_rules_and_unchecked_boxes_turn_off()
    {
        $this->actingAs($this->admin)->put(route('academic-settings.update'), [
            'project_min_completed_hours' => 30, 'project_min_level' => 3, 'project_max_members' => 4,
            'project_require_fees_settled' => 1, 'project_single_active' => 1, 'graduation_requires_project' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame(30.0, S::number(S::PROJECT_MIN_COMPLETED_HOURS));
        $this->assertTrue(S::flag(S::PROJECT_SINGLE_ACTIVE));
        $this->actingAs($this->admin)->get(route('academic-settings.edit'))->assertOk()->assertSee('ربط مشاريع التخرج');

        $this->actingAs($this->admin)->put(route('academic-settings.update'), ['project_single_active' => 0])->assertSessionHasNoErrors();
        $this->assertFalse(S::flag(S::PROJECT_SINGLE_ACTIVE));
        $this->assertNull(S::number(S::PROJECT_MIN_COMPLETED_HOURS));

        $this->actingAs($this->admin)->put(route('academic-settings.update'), ['project_min_level' => 0])->assertSessionHasErrors('project_min_level');
    }

    /** @test */
    public function the_members_pages_show_academic_info_and_flag_ineligible_students()
    {
        S::put(S::PROJECT_MIN_LEVEL, 5);
        [$user, $profile] = $this->makeStudent('طالب أكاديمي');
        ProjectMember::create(['project_id' => $this->project->id, 'student_id' => $user->id, 'role' => 'leader', 'joined_at' => now()]);
        [$other] = $this->makeStudent('طالب آخر');

        $this->actingAs($this->supervisor)->get(route('projects.members.index', $this->project))->assertOk()->assertSee('المستوى الأول');
        $this->actingAs($this->supervisor)->get(route('projects.members.create', $this->project))->assertOk()->assertSee('غير مؤهل');
        $this->actingAs($this->admin)->get(route('projects.members.create', $this->project))->assertOk()->assertSee('استثناء إداري');
        $this->actingAs($this->supervisor)->get(route('projects.members.create', $this->project))->assertDontSee('استثناء إداري');
    }

    private function passExisting($profile, $course, float $percent = 80): void
    {
        $section = $this->makeSection($this->faculty, $course);
        $enrollment = $this->enroll($profile, $section);
        $exam = $this->makeExam($section, ['max_score' => 100, 'weight' => 100, 'result_status' => 'published', 'published_at' => now()]);
        $this->setGrade($exam, $enrollment, $percent);
    }

    /** @test */
    public function the_completed_percent_rule_is_measured_against_the_study_plan_hours()
    {
        [$user, $profile] = $this->makeStudent();
        S::put(S::PASS_PERCENTAGE, 50);
        S::put(S::PROJECT_MIN_COMPLETED_PERCENT, 60);
        $svc = app(ProjectEligibility::class);
        \App\Models\StudyPlan::query()->delete(); // الفيكستشر يضع مقررًا في الخطة؛ نبدأ بخطة فارغة

        $this->assertStringContainsString('لا توجد خطة', implode(' ', $svc->reasons($user, $this->project)));

        // makeCourse يضيف المقرر إلى خطة المستوى: 3 + 3 + 4 = 10 ساعات
        $c1 = $this->makeCourse('PL1', 3);
        $c2 = $this->makeCourse('PL2', 3);
        $this->makeCourse('PL3', 4);
        $this->assertSame(10.0, app(\App\Services\AcademicRecordService::class)->planHours($profile));

        $this->passExisting($profile, $c1);
        $this->assertStringContainsString('أقل من المطلوب', implode(' ', $svc->reasons($user->fresh(), $this->project)), '3 من 10 = 30%');

        $this->passExisting($profile, $c2);
        $this->assertSame([], $svc->reasons($user->fresh(), $this->project), '6 من 10 = 60% تكفي (الحد شامل)');
    }

    /** @test */
    public function the_final_level_rule_requires_the_last_level_of_the_program()
    {
        [$user, $profile] = $this->makeStudent();
        $last = \App\Models\Level::create(['program_id' => $this->program->id, 'level_number' => 4, 'name' => 'المستوى الأخير']);
        S::put(S::PROJECT_REQUIRE_FINAL_LEVEL, true);

        $this->assertStringContainsString('المستوى الأخير', implode(' ', app(ProjectEligibility::class)->reasons($user, $this->project)));
        $profile->update(['current_level_id' => $last->id]);
        $this->assertSame([], app(ProjectEligibility::class)->reasons($user->fresh(), $this->project));
    }

    /** @test */
    public function the_recommended_rules_apply_without_overwriting_admin_choices_and_can_be_reset_by_the_admin()
    {
        S::put(S::PROJECT_MAX_MEMBERS, 7);
        S::applyRecommendedProjectRules(null, overwrite: false);
        $this->assertSame(7.0, S::number(S::PROJECT_MAX_MEMBERS), 'لا يُستبدل ما ضبطه المدير');
        $this->assertSame(60.0, S::number(S::PROJECT_MIN_COMPLETED_PERCENT));
        $this->assertTrue(S::flag(S::PROJECT_REQUIRE_FINAL_LEVEL));
        $this->assertTrue(S::flag(S::GRADUATION_REQUIRES_PROJECT));

        $this->actingAs($this->admin)->post(route('academic-settings.recommended-project-rules'))->assertSessionHas('success');
        $this->assertSame(4.0, S::number(S::PROJECT_MAX_MEMBERS));

        $this->actingAs($this->userWithRole('control'))->post(route('academic-settings.recommended-project-rules'))->assertForbidden();
    }

    /** @test */
    public function the_data_migration_is_skipped_under_tests_so_tests_stay_neutral()
    {
        $this->assertNull(S::number(S::PROJECT_MAX_MEMBERS));
        $migration = require database_path('migrations/2026_11_24_000000_apply_recommended_graduation_project_rules.php');
        $migration->up();
        $this->assertNull(S::number(S::PROJECT_MAX_MEMBERS));
        $migration->down();
    }
}
