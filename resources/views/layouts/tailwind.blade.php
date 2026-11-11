<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'كلية المجتمع عمران')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">

    <header class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ url('/dashboard') }}" class="text-lg font-bold text-indigo-700">كلية المجتمع عمران</a>
            <nav class="flex flex-wrap items-center gap-4 text-sm font-medium">
                <a href="{{ url('/dashboard') }}" class="hover:text-indigo-700">لوحة التحكم</a>
                @if (auth()->user()->hasPermission('enrollments.manage'))
                    <a href="{{ route('staff.enrollments.index') }}" class="hover:text-indigo-700">التسجيل في الشعب</a>
                    <a href="{{ route('staff.enrollments.students') }}" class="hover:text-indigo-700">سجل الطلاب</a>
                @endif
                @if (auth()->user()->hasPermission('enrollments.viewOwn'))
                    <a href="{{ route('student.enrollments.index') }}" class="hover:text-indigo-700">تسجيلاتي</a>
                    <a href="{{ route('student.schedule.index') }}" class="hover:text-indigo-700">جدولي</a>
                @endif
                @if (auth()->user()->hasPermission('enrollments.viewOwnSections'))
                    <a href="{{ route('faculty.enrollments.index') }}" class="hover:text-indigo-700">طلاب شعبي</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-red-600">خروج</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc pr-5 space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

</body>
</html>
