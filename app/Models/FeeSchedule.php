<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سعر الرسوم الدراسية لقسم عن سنة دراسية. لا سعر عام للكلية؛ لكل قسم سعره.
 */
class FeeSchedule extends Model
{
    const CURRENCIES = [
        'YER' => 'ريال يمني (قديم)',
        'USD' => 'دولار أمريكي',
    ];

    protected $fillable = ['department_id', 'academic_year_id', 'amount', 'currency', 'notes', 'updated_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public static function currencyLabel(string $currency): string
    {
        return self::CURRENCIES[$currency] ?? $currency;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
