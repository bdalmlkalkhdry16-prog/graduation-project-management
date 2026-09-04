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

        return view('student.service-requests.create');
    }

    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        $serviceRequest = auth()->user()->studentProfile->serviceRequests()->create($request->validated());

        return redirect()
            ->route('student.service-requests.show', $serviceRequest)
            ->with('success', 'تم إرسال طلبك بنجاح.');
    }

    public function show(StudentServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        return view('student.service-requests.show', compact('serviceRequest'));
    }
}