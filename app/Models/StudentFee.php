<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * رسوم طالب عن سنة دراسية (مبلغ وعملة مثبتان وقت التوليد).
 */
class StudentFee extends Model
{
    protected $fillable = ['student_profile_id', 'academic_year_id', 'fee_schedule_id', 'amount', 'currency', 'generated_by', 'results_released_at', 'results_released_by', 'results_release_reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'results_released_at' => 'datetime'];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeSchedule(): BelongsTo
    {
        return $this->belongsTo(FeeSchedule::class);
    }

    public function resultsReleased(): bool
    {
        return $this->results_released_at !== null;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** تُحسب المبالغ بالسنت/الفلس (أعداد صحيحة) لتفادي أخطاء الفاصلة العائمة، ولا تعتمد على bcmath. */
    public static function toCents(string|int|float $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public function paidCents(): int
    {
        return self::toCents($this->payments()->sum('amount'));
    }

    public function remainingCents(): int
    {
        return self::toCents($this->amount) - $this->paidCents();
    }

    public function paid(): float
    {
        return $this->paidCents() / 100;
    }

    public function remaining(): float
    {
        return max(0, $this->remainingCents()) / 100;
    }

    /** مسددة بالكامل (المتبقي ≤ صفر). */
    public function isSettled(): bool
    {
        return $this->remainingCents() <= 0;
    }
}
