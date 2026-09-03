@extends('portal.layout')

@section('title', 'كلية المجتمع عمران — الرئيسية')

@section('content')

    <section class="bg-indigo-700 text-white rounded-2xl px-8 py-16 text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">
            {{ $college->name ?? 'كلية المجتمع عمران' }}
        </h1>
        @if ($college?->description)
            <p class="text-indigo-100 max-w-2xl mx-auto">{{ $college->description }}</p>
        @endif
        <a href="{{ route('portal.admission') }}"
           class="inline-block mt-6 bg-white text-indigo-700 font-bold px-6 py-3 rounded-full hover:bg-indigo-50 transition">
            القبول والتسجيل
        </a>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-6">الأقسام</h2>

        @if ($departments->isEmpty())
            <p class="text-slate-500">لا توجد أقسام منشورة حاليًا.</p>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($departments as $department)
                    <a href="{{ route('portal.departments.show', $department) }}"
                       class="block bg-white rounded-xl border border-slate-200 p-6 hover:shadow-md hover:border-indigo-300 transition">
                        <h3 class="font-bold text-lg text-indigo-700 mb-2">{{ $department->name }}</h3>
                        <p class="text-sm text-slate-500">
                            {{ $department->specializations_count }} تخصص
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@endsection