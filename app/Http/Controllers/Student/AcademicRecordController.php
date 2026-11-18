<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AcademicRecordService;
use App\Services\TranscriptPdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AcademicRecordController extends Controller
{
    public function __construct(private AcademicRecordService $service)
    {
    }

    public function record(): View
    {
        return view('student.records.record', ['record' => $this->recordOrNull()]);
    }

    public function transcript(): View
    {
        return view('student.records.transcript', ['record' => $this->recordOrNull(), 'institution' => config('academic.institution_name')]);
    }

    public function pdf(TranscriptPdf $pdf): Response
    {
        $record = $this->recordOrNull();
        abort_unless($record, 404, 'لا يوجد ملف أكاديمي مرتبط بحسابك.');

        return response($pdf->render($record), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="transcript-'.$record['student']->number_student.'.pdf"',
        ]);
    }

    private function recordOrNull(): ?array
    {
        $profile = auth()->user()->studentProfile;

        return $profile ? $this->service->forStudent($profile) : null;
    }
}
