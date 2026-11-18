<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Services\AcademicRecordService;
use Illuminate\View\View;

class AcademicRecordController extends Controller
{
    /** السجل الأكاديمي الرسمي (المنشور فقط) لطالب — لمن يملك academic-records.viewAll. */
    public function show(AcademicRecordService $service, StudentProfile $studentProfile): View
    {
        $this->authorize('viewAcademicRecord', $studentProfile);

        return view('student.records.record', ['record' => $service->forStudent($studentProfile), 'staffView' => true]);
    }
}
