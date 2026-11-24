<?php

namespace App\Services;

use App\Models\StudentFee;
use Illuminate\Support\Collection;

/**
 * تقارير الرسوم. تُجمَّع بحسب السنة والقسم والعملة ولا تُخلط العملات أبدًا. الحساب بالسنت (أعداد صحيحة).
 */
class FeeReportService
{
    private function fees(?int $yearId, ?int $departmentId): Collection
    {
        return StudentFee::with(['studentProfile.user', 'academicYear', 'feeSchedule.department', 'payments', 'discounts'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($departmentId, fn ($q) => $q->whereHas('feeSchedule', fn ($s) => $s->where('department_id', $departmentId)))
            ->get();
    }

    /** ملخص لكل (سنة، قسم، عملة). */
    public function summary(?int $yearId = null, ?int $departmentId = null): Collection
    {
        return $this->fees($yearId, $departmentId)
            ->groupBy(fn (StudentFee $f) => $f->academic_year_id.'|'.($f->feeSchedule?->department_id ?? 0).'|'.$f->currency)
            ->map(function (Collection $group) {
                $first = $group->first();
                $gross = $group->sum(fn ($f) => StudentFee::toCents($f->amount));
                $discount = $group->sum(fn ($f) => $f->discountCents());
                $paid = $group->sum(fn ($f) => $f->paidCents());

                return [
                    'year' => $first->academicYear->name,
                    'year_value' => $first->academicYear->year,
                    'department' => $first->feeSchedule?->department?->name_ar ?? '—',
                    'currency' => $first->currency,
                    'students' => $group->count(),
                    'settled' => $group->filter->isSettled()->count(),
                    'unsettled' => $group->reject->isSettled()->count(),
                    'gross' => $gross / 100,
                    'discounts' => $discount / 100,
                    'net' => ($gross - $discount) / 100,
                    'paid' => $paid / 100,
                    'remaining' => ($gross - $discount - $paid) / 100,
                    'collection_rate' => ($gross - $discount) > 0 ? round($paid / ($gross - $discount) * 100, 1) : null,
                ];
            })
            ->sortBy([['year_value', 'desc'], ['department', 'asc'], ['currency', 'asc']])
            ->values();
    }

    /** غير المسددين (المتبقي > 0) مرتبين بالأكبر متبقيًا داخل كل عملة. */
    public function delinquent(?int $yearId = null, ?int $departmentId = null): Collection
    {
        return $this->fees($yearId, $departmentId)->reject->isSettled()
            ->map(fn (StudentFee $f) => [
                'fee' => $f,
                'student' => $f->studentProfile->user->name,
                'number' => $f->studentProfile->number_student,
                'year' => $f->academicYear->name,
                'department' => $f->feeSchedule?->department?->name_ar ?? '—',
                'currency' => $f->currency,
                'net' => $f->netCents() / 100,
                'paid' => $f->paidCents() / 100,
                'remaining' => $f->remainingCents() / 100,
                'released' => $f->resultsReleased(),
            ])
            ->sortBy([['currency', 'asc'], ['remaining', 'desc']])
            ->values();
    }

    /** إجماليات عامة لكل عملة. */
    public function totals(Collection $summary): Collection
    {
        return $summary->groupBy('currency')->map(fn ($rows) => [
            'students' => $rows->sum('students'),
            'net' => $rows->sum(fn ($r) => (int) round($r['net'] * 100)) / 100,
            'paid' => $rows->sum(fn ($r) => (int) round($r['paid'] * 100)) / 100,
            'remaining' => $rows->sum(fn ($r) => (int) round($r['remaining'] * 100)) / 100,
        ]);
    }
}
