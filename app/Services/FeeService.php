<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\FeeSchedule;
use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeService
{
    /**
     * يولّد رسوم طالب عن سنة دراسية من سعر قسمه (نسخة مثبتة). لا يكرر ولا يخمّن سعرًا غير موجود.
     */
    public function generateFor(StudentProfile $profile, AcademicYear $year, User $by): StudentFee
    {
        $departmentId = $profile->specialization?->department_id;
        $schedule = $departmentId ? FeeSchedule::where('department_id', $departmentId)->where('academic_year_id', $year->id)->first() : null;

        if (! $schedule) {
            throw ValidationException::withMessages(['fee' => 'لا يوجد سعر رسوم لقسم الطالب عن هذه السنة الدراسية.']);
        }

        if (StudentFee::where('student_profile_id', $profile->id)->where('academic_year_id', $year->id)->exists()) {
            throw ValidationException::withMessages(['fee' => 'رسوم هذا الطالب عن هذه السنة مولَّدة بالفعل.']);
        }

        $fee = StudentFee::create([
            'student_profile_id' => $profile->id, 'academic_year_id' => $year->id, 'fee_schedule_id' => $schedule->id,
            'amount' => $schedule->amount, 'currency' => $schedule->currency, 'generated_by' => $by->id,
        ]);

        ActivityLog::log($by->id, 'student_fee.generated', StudentFee::class, $fee->id, null, ['amount' => $fee->amount, 'currency' => $fee->currency]);

        return $fee;
    }

    /**
     * يولّد رسوم السنة لكل الطلاب ذوي الحالة الأكاديمية النشطة (اختياريًا لقسم واحد).
     *
     * @return array{created: int, skipped_existing: int, no_price: int}
     */
    public function generateForYear(AcademicYear $year, ?Department $department, User $by): array
    {
        $result = ['created' => 0, 'skipped_existing' => 0, 'no_price' => 0];

        StudentProfile::where('academic_status', 'active')
            ->with('specialization')
            ->when($department, fn ($q) => $q->whereHas('specialization', fn ($s) => $s->where('department_id', $department->id)))
            ->each(function (StudentProfile $profile) use ($year, $by, &$result) {
                try {
                    $this->generateFor($profile, $year, $by);
                    $result['created']++;
                } catch (ValidationException $e) {
                    str_contains($e->errors()['fee'][0], 'مولَّدة') ? $result['skipped_existing']++ : $result['no_price']++;
                }
            });

        return $result;
    }

    /** استثناء إداري برفع الحجب عن نتائج رسم معيّن (بسبب إلزامي) أو إلغاؤه. */
    public function setResultsRelease(StudentFee $fee, bool $release, ?string $reason, User $by): StudentFee
    {
        $fee->update($release
            ? ['results_released_at' => now(), 'results_released_by' => $by->id, 'results_release_reason' => $reason]
            : ['results_released_at' => null, 'results_released_by' => null, 'results_release_reason' => null]);
        ActivityLog::log($by->id, $release ? 'student_fee.results_released' : 'student_fee.results_release_revoked', StudentFee::class, $fee->id, null, ['reason' => $reason]);

        return $fee;
    }

    /** يسجّل دفعة بسند قبض؛ لا تتجاوز المتبقي، وبعملة الرسم نفسها. */
    public function recordPayment(StudentFee $fee, float|string $amount, string $paidAt, ?string $notes, User $by): Payment
    {
        return DB::transaction(function () use ($fee, $amount, $paidAt, $notes, $by) {
            $fee = StudentFee::whereKey($fee->id)->lockForUpdate()->firstOrFail();
            $cents = StudentFee::toCents($amount);

            if ($cents <= 0) {
                throw ValidationException::withMessages(['amount' => 'مبلغ الدفعة يجب أن يكون أكبر من صفر.']);
            }

            if ($cents > $fee->remainingCents()) {
                throw ValidationException::withMessages(['amount' => 'مبلغ الدفعة يتجاوز المتبقي ('.number_format(max(0, $fee->remainingCents()) / 100, 2).').']);
            }

            $payment = Payment::create(['student_fee_id' => $fee->id, 'amount' => $cents / 100, 'paid_at' => $paidAt, 'notes' => $notes, 'recorded_by' => $by->id]);
            $payment->update(['receipt_number' => 'RC-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT)]);

            ActivityLog::log($by->id, 'payment.recorded', Payment::class, $payment->id, null, ['amount' => $payment->amount, 'receipt' => $payment->receipt_number, 'student_fee_id' => $fee->id]);

            return $payment;
        });
    }
}
