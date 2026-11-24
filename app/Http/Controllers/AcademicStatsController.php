<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Services\AcademicRecordService;
use App\Services\AcademicStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicStatsController extends Controller
{
    public function index(Request $request, AcademicStatsService $stats): View
    {
        abort_unless($request->user()->hasPermission('academic-records.viewAll'), 403);

        $terms = AcademicTerm::with('academicYear')->get()
            ->sortByDesc(fn ($t) => [$t->academicYear->year ?? 0, ['first' => 1, 'second' => 2, 'summer' => 3][$t->semester] ?? 0])->values();
        $termId = $terms->contains('id', $request->integer('term')) ? $request->integer('term') : $stats->defaultTermId();

        return view('stats.academic', [
            'overview' => $stats->overview(),
            'terms' => $terms,
            'termId' => $termId,
            'rates' => $stats->coursePassRates($termId),
        ]);
    }
}
