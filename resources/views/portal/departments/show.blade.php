@extends('portal.layout')

@section('title', $department->name . ' — كلية المجتمع عمران')

@section('content')

    <a href="{{ route('portal.departments') }}" class="text-sm text-indigo-700 hover:underline mb-6 inline-block">
        &right; رجوع للأقسام
    </a>

    <h1 class="text-2xl font-bold mb-2">{{ $department->name }}</h1>

    @if ($department->description)
        <p class="text-slate-600 mb-8">{{ $department->description }}</p>
    @endif

    <h2 class="text-xl font-bold mb-4">التخصصات</h2>

    @if ($department->specializations->isEmpty())
        <p class="text-slate-500">لا توجد تخصصات منشورة لهذا القسم بعد.</p>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($department->specializations as $specialization)
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="font-bold text-lg mb-2">{{ $specialization->name }}</h3>

                    @if ($specialization->description)
                        <p class="text-sm text-slate-600 mb-4">{{ $specialization->description }}</p>
                    @endif

                    @if ($specialization->programs->isEmpty())
                        <p class="text-sm text-slate-400">لا توجد برامج منشورة لهذا التخصص بعد.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($specialization->programs as $program)
                                <li>
                                    <a href="{{ route('portal.programs.show', $program) }}"
                                       class="text-sm text-indigo-700 hover:underline">
                                        {{ $program->level === 'diploma' ? 'دبلوم' : 'بكالوريوس' }}
                                        {{ $specialization->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

@endsection