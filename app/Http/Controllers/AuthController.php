<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function studentLogin(): View
    {
        return view('auth.student-login');
    }

    public function studentRegister(): View
    {
        return view('auth.student-register');
    }

    public function staffLogin(): View
    {
        return view('auth.staff-login');
    }

    public function staffRegister(): View
    {
        return view('auth.staff-register');
    }

    public function registerStudent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_no' => ['required', 'string', 'max:50', 'unique:students,student_no'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'program' => ['required', 'in:Su Altı Teknolojisi,Sualtı Kaynak Teknolojisi'],
            'class_name' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'regex:/^[^@\s]+@iste\.edu\.tr$/i', 'unique:students,email'],
        ]);

        $data['approval_status'] = 'pending';
        Student::create($data);

        return redirect()->route('student.login')->with('status', 'Kaydiniz alindi. Sistem yoneticisi onayladiktan sonra giris yapabilirsiniz.');
    }

    public function registerStaff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_no' => ['required', 'string', 'max:50', 'unique:staff,staff_no'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'regex:/^[^@\s]+@iste\.edu\.tr$/i', 'unique:staff,email'],
        ]);

        $data['role_name'] = 'staff';
        $data['approval_status'] = 'pending';
        Staff::create($data);

        return redirect()->route('staff.login')->with('status', 'Kaydiniz alindi. Sistem yoneticisi onayladiktan sonra giris yapabilirsiniz.');
    }

    public function authenticateStudent(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'student_no' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $this->ensureIsNotRateLimited($request, 'student', $credentials['student_no']);

        $student = Student::where('student_no', $credentials['student_no'])->first();

        if (! $student || ! Hash::check($credentials['password'], $student->password)) {
            $this->hitLoginLimiter($request, 'student', $credentials['student_no']);

            throw ValidationException::withMessages([
                'student_no' => 'Ogrenci no veya sifre hatali.',
            ]);
        }

        if (! $student->isApproved()) {
            throw ValidationException::withMessages([
                'student_no' => 'Kaydiniz henuz sistem yoneticisi tarafindan onaylanmadi.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, 'student', $credentials['student_no']));

        $request->session()->regenerate();
        $request->session()->put('student_id', $student->id);
        $request->session()->forget('staff_id');

        return redirect()->route('student.dashboard');
    }

    public function authenticateStaff(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'staff_no' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $this->ensureIsNotRateLimited($request, 'staff', $credentials['staff_no']);

        $staff = Staff::where('staff_no', $credentials['staff_no'])->first();

        if (! $staff || ! Hash::check($credentials['password'], $staff->password)) {
            $this->hitLoginLimiter($request, 'staff', $credentials['staff_no']);

            throw ValidationException::withMessages([
                'staff_no' => 'Kurum no veya sifre hatali.',
            ]);
        }

        if (! $staff->isApproved()) {
            throw ValidationException::withMessages([
                'staff_no' => 'Kaydiniz henuz sistem yoneticisi tarafindan onaylanmadi.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, 'staff', $credentials['staff_no']));

        $request->session()->regenerate();
        $request->session()->put('staff_id', $staff->id);
        $request->session()->forget('student_id');

        return redirect()->route('staff.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function ensureIsNotRateLimited(Request $request, string $guard, string $identifier): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request, $guard, $identifier), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request, $guard, $identifier));

        throw ValidationException::withMessages([
            $guard === 'student' ? 'student_no' : 'staff_no' => "Cok fazla hatali giris denemesi. {$seconds} saniye sonra tekrar deneyin.",
        ]);
    }

    private function hitLoginLimiter(Request $request, string $guard, string $identifier): void
    {
        RateLimiter::hit($this->throttleKey($request, $guard, $identifier), 60);
    }

    private function throttleKey(Request $request, string $guard, string $identifier): string
    {
        return 'login|'.hash('sha256', $guard.'|'.Str::lower($identifier).'|'.$request->ip());
    }
}
