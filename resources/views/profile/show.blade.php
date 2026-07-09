@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ Storage::url(auth()->user()->profile_photo) }}" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px; font-size: 4rem;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                        <h4>{{ auth()->user()->name }}</h4>
                        <p class="text-muted">{{ auth()->user()->role_name }}</p>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">تعديل الملف الشخصي</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات الحساب</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">البريد الإلكتروني</th>
                                <td>{{ auth()->user()->email }}</td>
                            </tr>
                            <tr>
                                <th>رقم الجوال</th>
                                <td>{{ auth()->user()->phone ?? '-' }}</td>
                            </tr>
                            @if(auth()->user()->isStudent())
                                <tr>
                                    <th>الرقم الجامعي</th>
                                    <td>{{ auth()->user()->student_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>التخصص</th>
                                    <td>{{ auth()->user()->specialization->name_ar ?? '-' }}</td>
                                </tr>
                            @endif
                            @if(auth()->user()->isSupervisor())
                                <tr>
                                    <th>الرقم الوظيفي</th>
                                    <td>{{ auth()->user()->employee_id ?? '-' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>تاريخ التسجيل</th>
                                <td>{{ auth()->user()->created_at->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تسجيل دخول</th>
                                <td>{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
