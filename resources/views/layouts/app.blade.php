<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة مشاريع التخرج')</title>

    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome (رئيسي + احتياطي) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Toastr -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    <!-- Google Fonts (Tajawal) -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
        /* ========== التصميم الأساسي المتجاوب ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f8;
            overflow-x: hidden;
        }

        /* ========== القائمة الجانبية المحسّنة ========== */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e66 0%, #1a2a4f 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            z-index: 1020;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1.2rem;
            margin: 0.2rem 0.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link i {
            width: 28px;
            margin-left: 12px;
            font-size: 1.1rem;
            text-align: center;
        }
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(-4px);
        }
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        .sidebar-header h4 {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        /* ========== شريط التنقل العلوي ========== */
        .navbar-top {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1010;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e66;
            transition: 0.2s;
        }
        .sidebar-toggle:hover {
            color: #667eea;
            transform: scale(1.05);
        }

        /* ========== المحتوى الرئيسي ========== */
        .main-content {
            padding: 2rem;
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ========== البطاقات ========== */
        .card {
            border: none;
            border-radius: 1.25rem;
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 1.5rem;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #edf2f7;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1.25rem;
            overflow: hidden;
            position: relative;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" opacity="0.1"><path fill="white" d="M50,150 L150,150 L100,50 Z"/></svg>') no-repeat;
            background-size: 180px;
            background-position: bottom right;
        }
        .stat-card .card-body {
            position: relative;
            z-index: 2;
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 800;
        }

        /* ========== الأزرار ========== */
        .btn {
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #4c51bf 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(90, 103, 216, 0.3);
        }
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
        }
        .btn-outline-primary:hover {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }

        /* ========== الجداول ========== */
        .table {
            --bs-table-hover-bg: rgba(102, 126, 234, 0.05);
        }
        .table th {
            font-weight: 600;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .table td, .table th {
            padding: 1rem;
            vertical-align: middle;
        }

        /* ========== الشارات ========== */
        .badge {
            padding: 0.45rem 0.8rem;
            border-radius: 2rem;
            font-weight: 500;
        }

        /* ========== الصورة الرمزية ========== */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            transition: transform 0.2s;
        }
        .avatar:hover {
            transform: scale(1.05);
        }

        /* ========== الإشعارات ========== */
        .notification-item {
            transition: background 0.15s;
        }
        .notification-item.unread {
            background: #f8fafc;
        }
        .notification-badge {
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            padding: 0.2rem 0.45rem;
        }

        /* ========== التجاوب مع الجوال ========== */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                right: -280px;
                width: 280px;
                height: 100%;
                z-index: 1050;
                overflow-y: auto;
                transition: right 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            .sidebar.show {
                right: 0;
            }
            .main-content {
                padding: 1rem;
            }
            .navbar-top {
                padding: 0.5rem 1rem;
            }
            .stat-card h3 {
                font-size: 1.5rem;
            }
            .table {
                font-size: 0.85rem;
            }
            .btn {
                padding: 0.3rem 0.8rem;
                font-size: 0.85rem;
            }
            .card-header h5, .card-header h4 {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }

        /* شريط التمرير المخصص */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 sidebar" id="sidebar">
            <div class="sidebar-header text-center py-4">
                <h4 class="text-white mb-0 fw-bold">نظام المشاريع</h4>
                <p class="text-white-50 small mb-0 mt-1">إدارة مشاريع التخرج</p>
            </div>
            <nav class="nav flex-column px-3">
                @include('layouts.sidebar')
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-auto px-0">
            <!-- Top Navbar -->
            @auth
                <nav class="navbar-top d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <button class="sidebar-toggle" id="sidebarToggleBtn" aria-label="القائمة الجانبية">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('help') }}" class="btn btn-link text-dark text-decoration-none p-0" title="دليل الاستخدام">
                            <i class="fas fa-question-circle fa-lg text-secondary"></i>
                        </a>

                        <div class="dropdown">
                            <button class="btn btn-link p-0 position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fs-5 text-secondary"></i>
                                @php
                                    $unreadCount = auth()->user()->notifications()->unread()->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                                <div class="p-3 border-bottom">
                                    <h6 class="mb-0 fw-bold">الإشعارات</h6>
                                </div>
                                <div class="notification-list" style="max-height: 400px; overflow-y: auto;">
                                    @php
                                        $notifications = auth()->user()->notifications()->latest()->limit(10)->get();
                                    @endphp
                                    @forelse($notifications as $notification)
                                        <a href="{{ $notification->link ?? '#' }}" class="dropdown-item notification-item px-3 py-2 text-decoration-none {{ !$notification->is_read ? 'unread' : '' }}">
                                            <div class="d-flex gap-2">
                                                <div class="flex-shrink-0 mt-1">
                                                    @if($notification->type === 'success')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($notification->type === 'error')
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                    @elseif($notification->type === 'warning')
                                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                                    @else
                                                        <i class="fas fa-info-circle text-info"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold small">{{ $notification->title }}</div>
                                                    <p class="small text-muted mb-0">{{ Str::limit($notification->message, 60) }}</p>
                                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center py-4">
                                            <p class="text-muted mb-0">لا توجد إشعارات</p>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="p-2 text-center border-top">
                                    <a href="{{ route('notifications.index') }}" class="small text-primary text-decoration-none">عرض جميع الإشعارات</a>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-link d-flex align-items-center gap-2 text-dark text-decoration-none p-0" data-bs-toggle="dropdown">
                                @if(auth()->user()->profile_photo)
                                    <img src="{{ Storage::url(auth()->user()->profile_photo) }}" class="avatar" alt="avatar">
                                @else
                                    <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center fw-bold">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down small text-secondary"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i>الملف الشخصي</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            @endauth

            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-left",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif
    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif
    @if(session('info'))
        toastr.info("{{ session('info') }}");
    @endif
    @if(session('warning'))
        toastr.warning("{{ session('warning') }}");
    @endif

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>

@stack('scripts')
</body>
</html>