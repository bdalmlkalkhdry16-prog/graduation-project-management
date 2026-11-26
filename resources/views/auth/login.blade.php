<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - كلية المجتمع عمران</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="{{ asset('css/college-theme.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body{font-family:'Tajawal','Segoe UI',Tahoma,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;
            background:
              radial-gradient(circle at 15% 20%,rgba(184,150,46,.18),transparent 40%),
              linear-gradient(160deg,#14492a 0%,#0d3320 60%,#082316 100%);}
        body::before{content:'';position:fixed;inset:0;opacity:.07;pointer-events:none;
            background-image:repeating-linear-gradient(45deg,#fff 0 1px,transparent 1px 22px),repeating-linear-gradient(-45deg,#fff 0 1px,transparent 1px 22px);}
        .login-card{position:relative;background:#fff;border-radius:1rem;box-shadow:0 25px 50px rgba(0,0,0,.35);width:100%;max-width:440px;overflow:hidden;border-top:5px solid #b8962e}
        .login-header{text-align:center;padding:1.75rem 2rem 1rem}
        .login-header img{width:96px;margin-bottom:.75rem}
        .login-header h3{font-weight:800;color:#14492a;margin-bottom:.25rem;font-size:1.5rem}
        .login-header p{color:#5b6b61;margin:0;font-size:.95rem}
        .login-body{padding:1rem 2rem 2rem}
        .role-hint{display:flex;justify-content:center;gap:1.5rem;margin-bottom:1.25rem;color:#1f6b36;font-size:.8rem;text-align:center}
        .role-hint i{display:flex;width:42px;height:42px;margin:0 auto .25rem;border-radius:50%;background:#e8f2ea;align-items:center;justify-content:center;font-size:1.1rem}
        .form-control{border-radius:.5rem;padding:.7rem 1rem;border:1px solid #d5ddd7}
        .input-group-text{background:#f3f6f1;border:1px solid #d5ddd7}
        .btn-login{background:linear-gradient(135deg,#2a7d41,#14492a);border:none;color:#fff;padding:.75rem;border-radius:.5rem;font-weight:700}
        .btn-login:hover{background:linear-gradient(135deg,#1f6b36,#0d3320);color:#fff;box-shadow:0 6px 16px rgba(20,73,42,.35)}
        .register-link{color:#1f6b36;font-weight:600;text-decoration:none}
        .register-link:hover{text-decoration:underline}
        .alert{border-radius:.5rem}
        .login-footer{text-align:center;color:rgba(255,255,255,.6);font-size:.8rem;margin-top:1rem;position:relative}
    </style>
</head>
<body>
<div class="w-100" style="max-width:440px">
<div class="login-card">
    <div class="login-header">
        <img src="{{ asset('images/logo.png') }}" alt="شعار كلية المجتمع عمران">
        <h3>كلية المجتمع عمران</h3>
        <p>منصة الإدارة الأكاديمية والخدمات الإلكترونية</p>
    </div>
    <div class="login-body">
        <div class="role-hint">
            <div><i class="fas fa-user-tie"></i>موظف</div>
            <div><i class="fas fa-chalkboard-teacher"></i>عضو هيئة تدريس</div>
            <div><i class="fas fa-user-graduate"></i>طالب</div>
        </div>
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">البريد الإلكتروني</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">تذكرني</label>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
            </button>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <p class="mb-0">ليس لديك حساب؟ <a href="{{ route('register') }}" class="register-link">سجل الآن</a></p>
        </div>
    </div>
</div>
<div class="login-footer">&copy; {{ date('Y') }} كلية المجتمع عمران — جميع الحقوق محفوظة</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>
