@extends('portal.layout')

@section('title', ($program->level === 'diploma' ? 'دبلوم ' : 'بكالوريوس ') . $program->specialization->name . ' — كلية المجتمع عمران')

@section('content')

    <a href="{{ route('portal.departments.show', $program->specialization->department) }}"
       class="text-sm text-indigo-700 hover:underline mb-6 inline-block">
        &right; رجوع لـ{{ $program->specialization->department->name }}
    </a>

    <h1 class="text-2xl font-bold mb-2">
        {{ $program->level === 'diploma' ? 'دبلوم' : 'بكالوريوس' }}
        {{ $program->specialization->name }}
    </h1>

    <p class="text-slate-500 mb-8">
        القسم: {{ $program->specialization->department->name }}
        @if ($program->total_credit_hours)
            &middot; إجمالي الساعات المعتمدة: {{ $program->total_credit_hours }}
        @endif
    </p>

    <h2 class="text-xl font-bold mb-4">المستويات الدراسية</h2>

    @if ($program->levels->isEmpty())
        <p class="text-slate-500">لم تُنشر مستويات هذا البرنامج بعد.</p>
    @else
        <ol class="space-y-2">
            @foreach ($program->levels as $level)
                <li class="bg-white rounded-lg border border-slate-200 px-4 py-3">
                    {{ $level->name ?? 'المستوى ' . $level->level_number }}
                </li>
            @endforeach
        </ol>
    @endif

@endsection