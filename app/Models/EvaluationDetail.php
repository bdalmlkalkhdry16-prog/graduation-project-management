<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'criterion_name',
        'max_score',
        'score',
        'notes',
    ];

    protected $casts = [
        'max_score' => 'integer',
        'score' => 'integer',
    ];

    /**
     * العلاقة مع التقييم الرئيسي
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    /**
     * حساب النسبة المئوية لهذا المعيار
     */
    public function getPercentageAttribute()
    {
        if ($this->max_score == 0) {
            return 0;
        }

        return round(($this->score / $this->max_score) * 100, 2);
    }
}
