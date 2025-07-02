<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Login berhasil! Selamat datang, ' . Auth::user()->name);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->withInput($request->only('email'));
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'institution' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'institution.max' => 'Institusi maksimal 255 karakter.',
            'student_id.max' => 'ID mahasiswa maksimal 50 karakter.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'institution' => $request->institution,
                'student_id' => $request->student_id,
                'bio' => $request->bio,
            ]);

            // Fire registered event
            event(new Registered($user));

            // Auto login setelah registrasi
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name);

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Registrasi gagal. Silakan coba lagi.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        return view('profile', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|min:2',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'institution' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:50',
            'profile_image' => 'nullable|url|max:500',
            'bio' => 'nullable|string|max:1000',
        ], [
            'name.min' => 'Nama minimal 2 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'institution.max' => 'Institusi maksimal 255 karakter.',
            'student_id.max' => 'ID mahasiswa maksimal 50 karakter.',
            'profile_image.url' => 'URL gambar profil tidak valid.',
            'profile_image.max' => 'URL gambar profil maksimal 500 karakter.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validated = $validator->validated();

            // Hanya update field yang dikirim (tidak null)
            foreach ($validated as $key => $value) {
                if (!is_null($value)) {
                    $user->$key = $value;
                }
            }

            $user->save();

            return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui profil. Silakan coba lagi.');
        }
    }

    // ==============================================
    // PASSWORD MANAGEMENT METHODS
    // ==============================================

    /**
     * Change password untuk user yang sudah login dan tahu password lama
     * (Method utama untuk ganti password - menggabungkan dengan updatePassword yang lama)
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        // Cek apakah current password benar
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak benar.']);
        }

        try {
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return back()->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password. Silakan coba lagi.');
        }
    }

    /**
     * Show reset request form untuk user yang sudah login
     */
    public function showResetRequestForm()
    {
        return view('auth.reset-request-logged', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Send reset link untuk user yang sudah login
     */
    public function sendResetLinkForLoggedUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Pastikan email yang dimasukkan adalah email user yang sedang login
        if ($request->email !== Auth::user()->email) {
            return back()->withErrors(['email' => 'Email yang dimasukkan tidak sesuai dengan akun Anda.']);
        }

        try {
            // Send reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('success', 'Link reset password telah dikirim ke email Anda!');
            }

            return back()->withErrors(['email' => 'Gagal mengirim link reset password.']);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengirim email reset password.');
        }
    }

    /**
     * Alternative method name for updatePassword (alias untuk changePassword)
     * Untuk kompatibilitas dengan kode yang sudah ada
     */
    public function updatePassword(Request $request)
    {
        return $this->changePassword($request);
    }

    // ==============================================
    // FORGOT PASSWORD METHODS (untuk user yang tidak login)
    // ==============================================

    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak ditemukan dalam sistem kami.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Link reset password telah dikirim ke email Anda!');
        }

        return back()
            ->withErrors(['email' => 'Terjadi kesalahan saat mengirim email reset password.'])
            ->withInput();
    }

    /**
     * Show the reset password form
     */
    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Handle reset password request
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'Token reset password diperlukan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password Anda berhasil direset!');
        }

        return back()
            ->withErrors(['email' => 'Terjadi kesalahan saat mereset password.'])
            ->withInput();
    }

    // ==============================================
    // SESSION MANAGEMENT METHODS
    // ==============================================

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        return view('dashboard');
    }
}