<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function index(): View
    {
        $profile = auth()->user()->studentProfile;

        return view('student.fees.index', [
            'fees' => $profile ? $profile->studentFees()->with(['academicYear', 'payments', 'discounts'])->latest()->get() : collect(),
        ]);
    }
}
