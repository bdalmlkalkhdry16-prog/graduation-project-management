<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateServiceRequestStatusRequest;
use App\Models\StudentServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = StudentServiceRequest::with(['studentProfile.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->latest()
            ->paginate(20);

        return view('staff.service-requests.index', compact('requests'));
    }

    public function show(StudentServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        $serviceRequest->load(['studentProfile.user', 'handledBy']);

        return view('staff.service-requests.show', compact('serviceRequest'));
    }

    public function updateStatus(UpdateServiceRequestStatusRequest $request, StudentServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->update([
            ...$request->validated(),
            'handled_by' => auth()->id(),
        ]);

        return redirect()
            ->route('staff.service-requests.show', $serviceRequest)
            ->with('success', 'تم تحديث حالة الطلب.');
    }
}