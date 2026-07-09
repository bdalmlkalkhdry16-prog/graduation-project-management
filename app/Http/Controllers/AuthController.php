<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * عرض صفحة التسجيل
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // تحديث آخر تسجيل دخول
            $user->update(['last_login_at' => now()]);

            // تسجيل النشاط
            $this->logActivity('login', 'User', $user->id);

            // التوجيه حسب الدور
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ])->withInput();
    }

    /**
     * تسجيل مستخدم جديد
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'in:student,supervisor',
            'student_id' => 'required_if:role,student|nullable|unique:users',
            'employee_id' => 'required_if:role,supervisor|nullable|unique:users',
            'phone' => 'nullable|string|max:20',

        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'student',
            'student_id' => $request->student_id,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'specialization_id' => $request->specialization_id,
            'is_active' => true,
        ]);

        Auth::login($user);

        $this->logActivity('register', 'User', $user->id);

        return $this->redirectBasedOnRole($user);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $this->logActivity('logout', 'User', auth()->id());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * التوجيه حسب دور المستخدم
     */
  private function redirectBasedOnRole($user)
  {
      if ($user->isAdmin()) {
          return redirect()->route('dashboard');
      } elseif ($user->isSupervisor()) {
          return redirect()->route('dashboard');
      } else {
          return redirect()->route('dashboard');
      }
  }
}
