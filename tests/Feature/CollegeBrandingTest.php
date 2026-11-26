<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollegeBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_college_identity_without_old_colors(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();
        $this->assertStringContainsString('كلية المجتمع عمران', $html);
        $this->assertStringContainsString('images/logo.png', $html);
        $this->assertStringNotContainsString('667eea', $html);
        $this->assertStringNotContainsString('نظام إدارة مشاريع التخرج', $html);
    }

    public function test_layout_uses_logo_theme_and_green_palette(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($admin)->get(route('dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('images/logo.png', $html);
        $this->assertStringContainsString('css/college-theme.css', $html);
        $this->assertStringContainsString('نظام الإدارة الأكاديمية', $html);
        $this->assertStringNotContainsString('667eea', $html);
        $this->assertStringNotContainsString('2c3e66', $html);
    }

    public function test_assets_exist(): void
    {
        $this->assertFileExists(public_path('images/logo.png'));
        $this->assertFileExists(public_path('images/favicon.png'));
        $this->assertFileExists(public_path('css/college-theme.css'));
    }
}
