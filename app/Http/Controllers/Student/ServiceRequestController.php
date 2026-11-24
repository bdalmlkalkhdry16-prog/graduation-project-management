<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Models\StudentServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function index(): View
    {
        $requests = auth()->user()->studentProfile
            ?->serviceRequests()
            ->latest()
            ->paginate(20);

        return view('student.service-requests.index', compact('requests'));
    }

    public function create(): View
    {
        $this->authorize('create', StudentServiceRequest::class);

        return view('student.service-requests.create', [
            'types' => StudentServiceRequest::availableTypes(),
            'appealable' => ($profile = auth()->user()->studentProfile) ? StudentServiceRequest::appealableEnrollments($profile) : collect(),
        ]);
    }

    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        $serviceRequest = auth()->user()->studentProfile->serviceRequests()->create($request->validated());

        app(\App\Services\AcademicNotifier::class)->toPermissionHolders(
            'service-requests.manageAll', 'طلب خدمة جديد',
            \App\Models\StudentServiceRequest::typeLabel($serviceRequest->type).' — '.auth()->user()->name,
            'info', route('staff.service-requests.show', $serviceRequest)
        );

        return redirect()
            ->route('student.service-requests.show', $serviceRequest)
            ->with('success', 'تم إرسال طلبك بنجاح.');
    }

    public function show(StudentServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        return view('student.service-requests.show', compact('serviceRequest'));
    }

    public function transcript(StudentServiceRequest $serviceRequest, \App\Services\AcademicRecordService $records, \App\Services\TranscriptPdf $pdf)
    {
        $this->authorize('downloadTranscript', $serviceRequest);

        abort_if(app(\App\Services\FeeStatus::class)->withheldYears($serviceRequest->studentProfile)->isNotEmpty(), 403, 'لا يمكن تسلّم الكشف الرسمي قبل سداد الرسوم المستحقة.');

        $record = $records->forStudent($serviceRequest->studentProfile);

        return response($pdf->render($record), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="transcript-'.$record['student']->number_student.'.pdf"',
        ]);
    }
}
