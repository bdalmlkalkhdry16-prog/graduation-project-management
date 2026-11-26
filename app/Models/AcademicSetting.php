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
    // ربط مشاريع التخرج بالنظام الأكاديمي — كلها اختيارية، ولا قيود ما لم تُضبط (لا سياسة مفترضة)
    const PROJECT_MIN_COMPLETED_HOURS = 'project_min_completed_hours';
    const PROJECT_MIN_LEVEL = 'project_min_level';
    const PROJECT_REQUIRE_FEES_SETTLED = 'project_require_fees_settled';
    const PROJECT_MAX_MEMBERS = 'project_max_members';
    const PROJECT_SINGLE_ACTIVE = 'project_single_active';
    const GRADUATION_REQUIRES_PROJECT = 'graduation_requires_project';
    const PROJECT_MIN_COMPLETED_PERCENT = 'project_min_completed_percent';
    const PROJECT_REQUIRE_FINAL_LEVEL = 'project_require_final_level';

    /** القواعد الموصى بها لمشاريع التخرج (قرار الوكيل بتفويض المالك؛ قابلة للتعديل كلها من الإعدادات). */
    const RECOMMENDED_PROJECT_RULES = [
        self::PROJECT_REQUIRE_FINAL_LEVEL => true,       // الانضمام من المستوى الأخير في البرنامج
        self::PROJECT_MIN_COMPLETED_PERCENT => 60,       // إنجاز 60% من ساعات الخطة الدراسية على الأقل
        self::PROJECT_REQUIRE_FEES_SETTLED => true,      // لا انضمام مع رسوم غير مسددة
        self::PROJECT_MAX_MEMBERS => 4,                  // فريق حتى 4 طلاب
        self::PROJECT_SINGLE_ACTIVE => true,             // مشروع قائم واحد لكل طالب
        self::GRADUATION_REQUIRES_PROJECT => true,       // إفادة التخرج تتطلب مشروعًا مكتملًا
    ];

    /** يضبط القواعد الموصى بها. $overwrite=false يكتب المفقود فقط (لا يمس ما ضبطه المدير). */
    public static function applyRecommendedProjectRules(?int $userId = null, bool $overwrite = false): void
    {
        foreach (self::RECOMMENDED_PROJECT_RULES as $key => $value) {
            if ($overwrite || ! static::where('key', $key)->exists()) {
                static::put($key, $value, $userId);
            }
        }
    }

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

    public static function number(string $key): ?float
    {
        $value = static::get($key);

        return is_numeric($value) ? (float) $value : null;
    }

    public static function flag(string $key): bool
    {
        return static::get($key) === true;
    }

    /** سلم التقديرات مرتبًا تنازليًا بالحد الأدنى. */
    public static function gradeScale(): array
    {
        $scale = static::get(self::GRADE_SCALE, []);

        usort($scale, fn ($a, $b) => $b['min'] <=> $a['min']);

        return $scale;
    }
}
