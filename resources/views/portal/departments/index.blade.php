@extends('portal.layout')

@section('title', 'الأقسام — كلية المجتمع عمران')

@section('content')

    <h1 class="text-2xl font-bold mb-8">الأقسام والتخصصات</h1>

    @if ($departments->isEmpty())
        <p class="text-slate-500">لا توجد أقسام منشورة حاليًا.</p>
    @else
        <div class="space-y-8">
            @foreach ($departments as $department)
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <a href="{{ route('portal.departments.show', $department) }}"
                       class="text-xl font-bold text-indigo-700 hover:underline">
                        {{ $department->name }}
                    </a>

                    @if ($department->specializations->isNotEmpty())
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach ($department->specializations as $specialization)
                                <li class="bg-slate-100 text-slate-700 text-sm px-3 py-1 rounded-full">
                                    {{ $specialization->name }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-slate-500">لا توجد تخصصات منشورة لهذا القسم بعد.</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

@endsection