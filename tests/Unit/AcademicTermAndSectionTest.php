<?php

namespace Tests\Unit;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\FacultyProfile;
use App\Models\Section;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicTermAndSectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_academic_term_extends_an_existing_academic_year_without_altering_it()
    {
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026, 'is_active' => true]);

        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);

        $this->assertTrue($term->academicYear->is($year));
    }

    /** @test */
    public function the_same_semester_cannot_repeat_within_the_same_academic_year()
    {
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026]);
        AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);
    }

    /** @test */
    public function a_section_links_a_course_a_term_and_optionally_a_faculty_profile()
    {
        // إصلاح معماري بعد مراجعة Phase 3: الشعبة تشير لـ FacultyProfile
        // (هوية Phase 2 الأكاديمية)، وليس لـ users.id مباشرة.
        $specialization = Specialization::factory()->create();
        $course = Course::create([
            'specialization_id' => $specialization->id,
            'code' => 'CS101',
            'name_ar' => 'مقدمة في البرمجة',
            'credit_hours' => 3,
        ]);
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026]);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);
        $facultyUser = User::factory()->supervisor()->create();
        $facultyProfile = FacultyProfile::create(['user_id' => $facultyUser->id]);

        $section = Section::create([
            'course_id' => $course->id,
            'academic_term_id' => $term->id,
            'faculty_profile_id' => $facultyProfile->id,
            'code' => 'أ',
            'capacity' => 30,
        ]);

        $this->assertTrue($section->course->is($course));
        $this->assertTrue($section->academicTerm->is($term));
        $this->assertTrue($section->faculty->is($facultyProfile));
        // الوصول لحساب المستخدم الأساسي يبقى ممكنًا عبر العلاقة المتسلسلة
        $this->assertTrue($section->faculty->user->is($facultyUser));
    }

    /** @test */
    public function assigning_a_section_to_a_user_without_a_faculty_profile_is_rejected_at_the_database_level()
    {
        $specialization = Specialization::factory()->create();
        $course = Course::create([
            'specialization_id' => $specialization->id,
            'code' => 'CS102',
            'name_ar' => 'برمجة 2',
            'credit_hours' => 3,
        ]);
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026]);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // 9999 لا يقابله أي صف في faculty_profiles → يجب أن يُرفَض بقيد FK
        Section::create([
            'course_id' => $course->id,
            'academic_term_id' => $term->id,
            'faculty_profile_id' => 9999,
            'code' => 'ب',
        ]);
    }
}
