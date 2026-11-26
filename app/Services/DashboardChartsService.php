<?php

namespace App\Services;

use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\StudentServiceRequest;

/** بيانات الرسوم البيانية للوحة المدير (أعداد فعلية فقط). */
class DashboardChartsService
{
    public function forAdmin(): array
    {
        $bySpec = StudentProfile::query()
            ->selectRaw('specialization_id, count(*) as c')
            ->groupBy('specialization_id')
            ->pluck('c', 'specialization_id');
        $names = Specialization::whereIn('id', $bySpec->keys())->pluck('name_ar', 'id');

        $donut = ['labels' => [], 'values' => []];
        foreach ($bySpec as $id => $count) {
            $donut['labels'][] = $names[$id] ?? 'غير محدد';
            $donut['values'][] = (int) $count;
        }

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[now()->startOfMonth()->subMonths($i)->format('Y-m')] = 0;
        }
        StudentServiceRequest::where('created_at', '>=', now()->startOfMonth()->subMonths(5))
            ->get(['created_at'])
            ->each(function ($r) use (&$months) {
                $k = $r->created_at->format('Y-m');
                if (isset($months[$k])) {
                    $months[$k]++;
                }
            });

        return [
            'donut' => $donut,
            'line' => ['labels' => array_keys($months), 'values' => array_values($months)],
            'latest_requests' => StudentServiceRequest::with('studentProfile.user')->latest()->limit(6)->get(),
            'open_requests' => StudentServiceRequest::whereIn('status', StudentServiceRequest::OPEN_STATUSES)->count(),
        ];
    }
}
