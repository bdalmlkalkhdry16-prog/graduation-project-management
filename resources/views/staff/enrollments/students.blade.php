@extends('layouts.app')
@section('title', 'سجل تسجيلات الطلاب')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">سجل تسجيلات الطلاب</h2>
    <form method="GET" class="input-group mb-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الرقم الأكاديمي" class="form-control">
        <button class="btn btn-primary"><i class="fas fa-search me-1"></i> بحث</button>
    </form>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>الرقم الأكاديمي</th><th>الاسم</th><th>المستوى</th><th></th></tr></thead>
            <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->number_student }}</td>
                    <td>{{ $student->user->name }}</td>
                    <td>{{ $student->currentLevel->name ?? '—' }}</td>
                    <td><a href="{{ route('staff.enrollments.history', $student) }}" class="btn btn-sm btn-outline-primary">سجل التسجيلات</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">لا نتائج.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
    <div class="mt-3">{{ $students->links() }}</div>
</div>
@endsection
