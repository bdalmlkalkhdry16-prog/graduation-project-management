<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * إعدادات أكاديمية قابلة للضبط من المؤسسة (لا قيم افتراضية مفترضة):
 *  - pass_percentage: نسبة النجاح في المقرر (null = غير محددة)
 *  - appeal_window_days: مهلة التظلم بالأيام من نشر النتيجة (null = بلا مهلة)
 *  - grade_scale: سلم التقديرات [{letter, min, points?}] (فارغ = لا تقديرات ولا معدل)
 */
class AcademicSetting extends Model
{
    const PASS_PERCENTAGE = 'pass_percentage';
    const GRADE_SCALE = 'grade_scale';
    const APPEAL_WINDOW_DAYS = 'appeal_window_days';

    protected $fillable = ['key', 'value', 'updated_by'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();

        return $row && $row->value !== null ? $row->value : $default;
    }

    public static function put(string $key, mixed $value, ?int $userId = null): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $userId]);
    }

    /** نسبة النجاح كرقم أو null. */
    public static function passPercentage(): ?float
    {
        $value = static::get(self::PASS_PERCENTAGE);

        return is_numeric($value) ? (float) $value : null;
    }

    /** مهلة التظلم بالأيام أو null (بلا مهلة ما لم تُضبط). */
    public static function appealWindowDays(): ?int
    {
        $value = static::get(self::APPEAL_WINDOW_DAYS);

        return is_numeric($value) ? (int) $value : null;
    }

    /** سلم التقديرات مرتبًا تنازليًا بالحد الأدنى. */
    public static function gradeScale(): array
    {
        $scale = static::get(self::GRADE_SCALE, []);

        usort($scale, fn ($a, $b) => $b['min'] <=> $a['min']);

        return $scale;
    }
}
