<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        {
    // Jika user sudah login dan mengakses forgot password
    if (auth()->check()) {
        // Bisa redirect ke profile dengan pesan
        return redirect()->route('profile')->with('info', 'Anda sudah login. Gunakan form "Ubah Sandi" untuk mengubah password.');
    }
    
    return view('auth.forgot-password');
}
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak ditemukan dalam sistem kami.'
        ]);

        // Send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with([
                'success' => 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.'
            ]);
        }

        return back()->withErrors([
            'email' => 'Terjadi kesalahan saat mengirim email. Silakan coba lagi.'
        ]);
    }
}