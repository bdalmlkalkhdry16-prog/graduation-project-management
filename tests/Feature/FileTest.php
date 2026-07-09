<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function student_can_upload_file_to_project()
    {
        Storage::fake('public');
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $project = Project::factory()->create([
            'supervisor_id' => $supervisor->id,
            'status' => Project::STATUS_APPROVED,
        ]);
        $project->students()->attach($student->id, ['role' => 'member']);

        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($student)
            ->post(route('projects.files.store', $project), [
                'file' => $file,
                'file_category' => 'report',
            ]);

        $response->assertRedirect(route('projects.files.index', $project));
        $this->assertDatabaseHas('project_files', [
            'project_id' => $project->id,
            'file_name' => 'document.pdf',
            'uploaded_by' => $student->id,
        ]);
        Storage::disk('public')->assertExists("projects/{$project->id}/files/{$file->hashName()}");
    }

    /** @test */
    public function supervisor_can_approve_file()
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();
        $project = Project::factory()->create(['supervisor_id' => $supervisor->id]);
        $file = $project->files()->create([
            'file_name' => 'test.pdf',
            'file_path' => 'dummy/path.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 100,
            'file_category' => 'report',
            'uploaded_by' => $student->id,
            'is_approved' => false,
        ]);

        $response = $this->actingAs($supervisor)
            ->post(route('projects.files.approve', $file));

        $response->assertRedirect();
        $this->assertDatabaseHas('project_files', [
            'id' => $file->id,
            'is_approved' => true,
        ]);
    }
}