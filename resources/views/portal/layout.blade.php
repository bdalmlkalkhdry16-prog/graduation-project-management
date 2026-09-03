<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'كلية المجتمع عمران')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans">

    <header class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('portal.home') }}" class="text-xl font-bold text-indigo-700">
                كلية المجتمع عمران
            </a>
            <nav class="flex gap-6 text-sm font-medium">
                <a href="{{ route('portal.home') }}" class="hover:text-indigo-700">الرئيسية</a>
                <a href="{{ route('portal.departments') }}" class="hover:text-indigo-700">الأقسام</a>
                <a href="{{ route('portal.admission') }}" class="hover:text-indigo-700">القبول والتسجيل</a>
                <a href="{{ route('login') }}" class="hover:text-indigo-700">تسجيل الدخول</a>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-10">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-slate-500 text-center">
            &copy; {{ now()->year }} كلية المجتمع عمران
        </div>
    </footer>

</body>
</html>