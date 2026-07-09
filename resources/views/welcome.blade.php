<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة مشاريع التخرج</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s;
            border: none;
            border-radius: 1rem;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .btn-custom {
            background: white;
            color: #667eea;
            border-radius: 2rem;
            padding: 0.75rem 2rem;
            font-weight: bold;
        }
        .btn-custom:hover {
            transform: scale(1.05);
            background: #f8f9fa;
        }
    </style>
</head>
<body>
<!-- Hero Section -->
<section class="hero text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">نظام إدارة مشاريع التخرج</h1>
        <p class="lead mb-5">منصة متكاملة لإدارة مشاريع التخرج في كليات المجتمع</p>
        <div class="d-flex gap-3 justify-content-center">
            @guest
                <a href="{{ route('login') }}" class="btn btn-custom btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i> إنشاء حساب
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-custom btn-lg">
                    <i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم
                </a>
            @endguest
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">مميزات النظام</h2>
            <p class="text-muted">نظام متكامل لإدارة مشاريع التخرج بكل سهولة</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card h-100 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-folder-open fa-3x text-primary mb-3"></i>
                        <h5>أرشفة المشاريع</h5>
                        <p class="text-muted">تخزين جميع مشاريع التخرج في قاعدة بيانات منظمة وسهلة البحث</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-star fa-3x text-warning mb-3"></i>
                        <h5>تقييم المشاريع</h5>
                        <p class="text-muted">نظام متكامل لتقييم المشاريع حسب معايير محددة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h5>حماية الملكية الفكرية</h5>
                        <p class="text-muted">حفظ حقوق الطلاب ومنع تكرار الأفكار</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white-50 py-4">
    <div class="container text-center">
        <p class="mb-0">&copy; {{ date('Y') }} نظام إدارة مشاريع التخرج. جميع الحقوق محفوظة</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
