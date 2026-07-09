<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'supervisor_id',
        'creativity_score',
        'implementation_score',
        'documentation_score',
        'presentation_score',
        'total_percentage',
        'strengths',
        'weaknesses',
        'recommendations',
        'status',
        'evaluated_at',
    ];

    protected $casts = [
        'creativity_score' => 'integer',
        'implementation_score' => 'integer',
        'documentation_score' => 'integer',
        'presentation_score' => 'integer',
        'total_percentage' => 'float',
        'evaluated_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_FINALIZED = 'finalized';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function details()
    {
        return $this->hasMany(EvaluationDetail::class);
    }

    /**
     * حساب النسبة الإجمالية للتقييم باستخدام الأوزان:
     * الإبداع 40%، التنفيذ 30%، التوثيق 20%، العرض 10%
     */
    public function calculateTotalPercentage()
    {
        if (!$this->creativity_score || !$this->implementation_score ||
            !$this->documentation_score || !$this->presentation_score) {
            return null;
        }

        $creativity = $this->creativity_score * 0.40;
        $implementation = $this->implementation_score * 0.30;
        $documentation = $this->documentation_score * 0.20;
        $presentation = $this->presentation_score * 0.10;

        // تصحيح: جمع جميع الأجزاء
        $percentage = round($creativity + $implementation + $documentation + $presentation, 2);

        $this->update(['total_percentage' => $percentage]);

        return $percentage;
    }

    public function getStatusNameAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SUBMITTED => 'تم التقديم',
            self::STATUS_FINALIZED => 'نهائي',
            default => 'غير محدد'
        };
    }

    public function isFinalized()
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }
}