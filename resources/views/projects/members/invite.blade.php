@extends('layouts.app')

@section('title', 'دعوة طالب')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">دعوة طالب للانضمام إلى المشروع: {{ $project->title_ar }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('projects.members.send-invite', $project) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني للطالب</label>
                                <input type="email" name="student_email" class="form-control @error('student_email') is-invalid @enderror" required>
                                @error('student_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> سيتم إرسال إشعار للطالب بدعوة للانضمام.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">إرسال الدعوة</button>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
