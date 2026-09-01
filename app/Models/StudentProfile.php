<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 2 — Student Central Profile.
 * program_id / current_level_id: أُضيفا في Phase 3 (Academic Structure).
 */
class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'number_student',
        'specialization_id',
        'program_level',
        'level',
        'admission_year',
        'academic_status',
        'program_id',
        'current_level_id',
    ];

    /**
     * إصلاح معماري بعد مراجعة Phase 3: يمنع حفظ بيانات أكاديمية متضاربة
     * على مستوى Model (لا يوجد قيد DB مباشر ممكن هنا لأن التحقق يحتاج
     * جلب سجل مرتبط للمقارنة، وهذا غير قابل للتعبير كقيد CHECK بسيط
     * متوافق بين SQLite وMySQL معًا). حدث saving() نقطة مركزية واحدة
     * تغطي create() وupdate() معًا مهما كان مصدر الاستدعاء:
     *
     * 1) current_level_id يجب أن ينتمي لنفس program_id.
     * 2) specialization_id يجب أن يطابق program.specialization_id
     *    (عند تحديد الاثنين معًا). specialization_id يبقى صالحًا
     *    ومطلوبًا بمفرده قبل تحديد program_id رسميًا (حالة القبول
     *    الأولي) — التحقق لا يُفعَّل إلا عند وجود الاثنين معًا.
     */
    protected static function booted(): void
    {
        static::saving(function (StudentProfile $profile) {
            if ($profile->program_id && $profile->current_level_id) {
                $level = Level::find($profile->current_level_id);

                if ($level && $level->program_id !== $profile->program_id) {
                    throw new \InvalidArgumentException(
                        'current_level_id يجب أن ينتمي لنفس البرنامج (program_id) المسجَّل للطالب.'
                    );
                }
            }

            if ($profile->program_id && $profile->specialization_id) {
                $program = Program::find($profile->program_id);

                if ($program && $program->specialization_id !== $profile->specialization_id) {
                    throw new \InvalidArgumentException(
                        'specialization_id يجب أن يطابق تخصص البرنامج (program.specialization_id) المسجَّل للطالب.'
                    );
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }
}
