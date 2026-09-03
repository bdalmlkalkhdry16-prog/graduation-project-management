@extends('portal.layout')

@section('title', 'القبول والتسجيل — كلية المجتمع عمران')

@section('content')

    <div class="max-w-xl mx-auto text-center bg-white rounded-2xl border border-slate-200 p-10">
        <h1 class="text-2xl font-bold mb-4">القبول والتسجيل</h1>
        <p class="text-slate-600 mb-8">
            يتم القبول والتسجيل للطلاب الجدد عبر بوابة التنسيق الرسمية،
            وليس من خلال هذا الموقع مباشرة.
        </p>
        <a href="{{ $admissionUrl }}" target="_blank" rel="noopener noreferrer"
           class="inline-block bg-indigo-700 text-white font-bold px-6 py-3 rounded-full hover:bg-indigo-800 transition">
            الانتقال إلى بوابة التنسيق
        </a>
    </div>

@endsection