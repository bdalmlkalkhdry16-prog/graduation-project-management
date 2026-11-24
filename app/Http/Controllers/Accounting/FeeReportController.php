<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\FeeReportExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\StudentFee;
use App\Services\FeeReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class FeeReportController extends Controller
{
    public function __construct(private FeeReportService $reports)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentFee::class);
        [$year, $dept] = $this->filters($request);
        $summary = $this->reports->summary($year, $dept);

        return view('accounting.reports.fees', [
            'summary' => $summary,
            'totals' => $this->reports->totals($summary),
            'delinquent' => $this->reports->delinquent($year, $dept),
            'years' => AcademicYear::orderByDesc('year')->get(),
            'departments' => Department::orderBy('name_ar')->get(),
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', StudentFee::class);
        [$year, $dept] = $this->filters($request);

        if ($request->input('view') === 'delinquent') {
            $export = new FeeReportExport(
                ['الطالب', 'رقم القيد', 'السنة', 'القسم', 'العملة', 'الصافي', 'المدفوع', 'المتبقي', 'رُفع الحجب'],
                $this->reports->delinquent($year, $dept)->map(fn ($r) => [$r['student'], $r['number'], $r['year'], $r['department'], $r['currency'], $r['net'], $r['paid'], $r['remaining'], $r['released'] ? 'نعم' : 'لا'])->all()
            );
            $name = 'fee-delinquent';
        } else {
            $export = new FeeReportExport(
                ['السنة', 'القسم', 'العملة', 'الطلاب', 'مسددون', 'غير مسددين', 'المستحق', 'الخصومات', 'الصافي', 'المدفوع', 'المتبقي', 'نسبة التحصيل %'],
                $this->reports->summary($year, $dept)->map(fn ($r) => [$r['year'], $r['department'], $r['currency'], $r['students'], $r['settled'], $r['unsettled'], $r['gross'], $r['discounts'], $r['net'], $r['paid'], $r['remaining'], $r['collection_rate']])->all()
            );
            $name = 'fee-summary';
        }

        return Excel::download($export, $name.'-'.now()->format('Ymd').'.xlsx');
    }

    private function filters(Request $request): array
    {
        return [$request->integer('academic_year_id') ?: null, $request->integer('department_id') ?: null];
    }
}
