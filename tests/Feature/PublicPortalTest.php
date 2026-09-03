<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Department;
use App\Models\Level;
use App\Models\Program;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 4 — Public Portal.
 * كل الصفحات هنا بلا تسجيل دخول (زائر) — لا استخدام لـ actingAs() إطلاقًا.
 */
class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_portal_home_page_is_publicly_accessible_and_shows_active_departments()
    {
        $college = College::factory()->create(['name_ar' => 'كلية المجتمع عمران', 'name_en' => null]);
        $activeDept = Department::factory()->create([
            'college_id' => $college->id,
            'name_ar' => 'قسم تقنية المعلومات', 'name_en' => null,
            'is_active' => true,
        ]);
        $inactiveDept = Department::factory()->create([
            'college_id' => $college->id,
            'name_ar' => 'قسم مُعطَّل', 'name_en' => null,
            'is_active' => false,
        ]);

        $response = $this->get(route('portal.home'));

        $response->assertOk();
        $response->assertSee('كلية المجتمع عمران');
        $response->assertSee('قسم تقنية المعلومات');
        $response->assertDontSee('قسم مُعطَّل');
    }

    /** @test */
    public function the_departments_index_lists_specializations_under_each_department()
    {
        $department = Department::factory()->create(['name_ar' => 'قسم التمريض', 'name_en' => null, 'is_active' => true]);
        Specialization::factory()->create([
            'department_id' => $department->id,
            'name_ar' => 'تمريض عام', 'name_en' => null,
            'is_active' => true,
        ]);

        $response = $this->get(route('portal.departments'));

        $response->assertOk();
        $response->assertSee('قسم التمريض');
        $response->assertSee('تمريض عام');
    }

    /** @test */
    public function a_department_show_page_lists_its_programs()
    {
        $department = Department::factory()->create(['name_ar' => 'قسم المحاسبة', 'name_en' => null, 'is_active' => true]);
        $specialization = Specialization::factory()->create([
            'department_id' => $department->id,
            'name_ar' => 'محاسبة', 'name_en' => null,
            'is_active' => true,
        ]);
        Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);

        $response = $this->get(route('portal.departments.show', $department));

        $response->assertOk();
        $response->assertSee('قسم المحاسبة');
        $response->assertSee('بكالوريوس');
    }

    /** @test */
    public function an_inactive_department_show_page_returns_404()
    {
        $department = Department::factory()->create(['is_active' => false]);

        $response = $this->get(route('portal.departments.show', $department));

        $response->assertNotFound();
    }

    /** @test */
    public function a_program_show_page_lists_its_levels()
    {
        $department = Department::factory()->create(['is_active' => true]);
        $specialization = Specialization::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $program = Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
        Level::create(['program_id' => $program->id, 'level_number' => 1, 'name' => 'المستوى الأول']);

        $response = $this->get(route('portal.programs.show', $program));

        $response->assertOk();
        $response->assertSee('المستوى الأول');
    }

    /** @test */
    public function the_admission_page_links_to_the_official_external_coordination_portal()
    {
        $response = $this->get(route('portal.admission'));

        $response->assertOk();
        $response->assertSee('https://tanseek.net/crgate.php?cg=12', false);
    }

    /** @test */
    public function portal_pages_require_no_authentication_at_all()
    {
        // لا $this->actingAs() في أي اختبار بهذا الملف — تأكيد إضافي صريح
        $this->assertGuest();

        $this->get(route('portal.home'))->assertOk();
        $this->get(route('portal.departments'))->assertOk();
        $this->get(route('portal.admission'))->assertOk();
    }
}