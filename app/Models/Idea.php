<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Idea extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'abstract_ar',
        'abstract_en',
        'keywords',
        'student_id',
        'specialization_id',
        'status',
        'review_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'project_id',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // التحقق من تكرار الفكرة (في الأفكار المقبولة أو في المشاريع المعتمدة)
    public static function checkDuplicate($title_ar, $keywords = null)
    {
        // التحقق في الأفكار المقبولة
        $existsInIdeas = self::where('status', self::STATUS_APPROVED)
            ->where(function ($q) use ($title_ar, $keywords) {
                $q->where('title_ar', 'like', "%$title_ar%");
                if ($keywords) {
                    $q->orWhere('keywords', 'like', "%$keywords%");
                }
            })->exists();

        // التحقق في المشاريع المعتمدة سابقاً
        $existsInProjects = Project::whereIn('status', [Project::STATUS_APPROVED, Project::STATUS_COMPLETED])
            ->where(function ($q) use ($title_ar, $keywords) {
                $q->where('title_ar', 'like', "%$title_ar%");
                if ($keywords) {
                    $q->orWhere('keywords', 'like', "%$keywords%");
                }
            })->exists();

        return $existsInIdeas || $existsInProjects;
    }

    // تحويل الفكرة إلى مشروع بعد الموافقة
    public function convertToProject()
    {
        $project = Project::create([
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'abstract_ar' => $this->abstract_ar,
            'abstract_en' => $this->abstract_en,
            'keywords' => $this->keywords,
            'specialization_id' => $this->specialization_id,
            'status' => Project::STATUS_APPROVED,
            'idea_approved' => true,
            'idea_submitted_at' => $this->submitted_at,
            'idea_review_notes' => $this->review_notes,
        ]);

        // ربط الطالب بالمشروع كقائد
        ProjectMember::create([
            'project_id' => $project->id,
            'student_id' => $this->student_id,
            'role' => 'leader',
            'joined_at' => now(),
        ]);

        // ربط الفكرة بالمشروع
        $this->update(['project_id' => $project->id]);

        return $project;
    }
}