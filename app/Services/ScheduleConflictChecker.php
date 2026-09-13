<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Section;

/**
 * يفحص تعارض القاعة/المدرس/الشعبة عند إضافة أو تعديل جدول.
 * التداخل الزمني: start1 < end2 AND start2 < end1 لنفس اليوم.
 * الفحص مقيّد بنفس الفصل الدراسي (عبر section.academic_term_id).
 */
class ScheduleConflictChecker
{
    /**
     * @return array<string> رسائل التعارض (فارغة = لا يوجد تعارض)
     */
    public function check(
        int $sectionId,
        int $roomId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreScheduleId = null
    ): array {
        $section = Section::findOrFail($sectionId);
        $termId = $section->academic_term_id;
        $facultyProfileId = $section->faculty_profile_id;

        $conflicts = [];

        $overlapping = function ($query) use ($dayOfWeek, $startTime, $endTime, $ignoreScheduleId) {
            $query->where('day_of_week', $dayOfWeek)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);

            if ($ignoreScheduleId) {
                $query->where('id', '!=', $ignoreScheduleId);
            }
        };

        $roomConflict = Schedule::where('room_id', $roomId)
            ->whereHas('section', fn ($q) => $q->where('academic_term_id', $termId))
            ->where($overlapping)
            ->exists();

        if ($roomConflict) {
            $conflicts[] = 'يوجد تعارض: القاعة محجوزة في نفس اليوم والوقت لشعبة أخرى.';
        }

        if ($facultyProfileId) {
            $facultyConflict = Schedule::whereHas('section', function ($q) use ($facultyProfileId, $termId) {
                $q->where('faculty_profile_id', $facultyProfileId)
                    ->where('academic_term_id', $termId);
            })
                ->where($overlapping)
                ->exists();

            if ($facultyConflict) {
                $conflicts[] = 'يوجد تعارض: عضو هيئة التدريس لديه محاضرة أخرى في نفس اليوم والوقت.';
            }
        }

        $sectionConflict = Schedule::where('section_id', $sectionId)
            ->where($overlapping)
            ->exists();

        if ($sectionConflict) {
            $conflicts[] = 'يوجد تعارض: هذه الشعبة لديها موعد آخر متداخل في نفس اليوم والوقت.';
        }

        return $conflicts;
    }
}