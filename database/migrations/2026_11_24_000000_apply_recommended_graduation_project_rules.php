<?php

use App\Models\AcademicSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // قواعد مشاريع التخرج الموصى بها (تفويض المالك للوكيل): تُكتب إن لم تكن مضبوطة، ولا تمس قيمًا ضبطها المدير.
        // لا تُطبَّق أثناء الاختبارات الآلية حتى تبقى سلوكيات الاختبار محايدة.
        if (app()->runningUnitTests()) {
            return;
        }

        AcademicSetting::applyRecommendedProjectRules(null, overwrite: false);
    }

    public function down(): void
    {
        // بيانات إعدادات فقط؛ لا شيء يُحذف عند التراجع حتى لا تُفقد قيم عدّلها المدير.
    }
};
