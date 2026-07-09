<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupervisorChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'student_id', 'current_supervisor_id', 'proposed_supervisor_id',
        'reason', 'status', 'admin_feedback', 'reviewed_at', 'reviewed_by'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function currentSupervisor()
    {
        return $this->belongsTo(User::class, 'current_supervisor_id');
    }

    public function proposedSupervisor()
    {
        return $this->belongsTo(User::class, 'proposed_supervisor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}