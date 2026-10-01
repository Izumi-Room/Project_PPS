<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password request form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset token/link.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('status', 'Jika email terdaftar di sistem, tautan reset kata sandi telah dikirim.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        // Store direct reset link in session for local development / testing convenience
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        return back()
            ->with('status', 'Tautan pemulihan kata sandi berhasil dibuat.')
            ->with('dev_reset_url', $resetUrl);
    }

    /**
     * Display the password reset view.
     */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email ?? '',
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token reset kata sandi tidak valid atau sudah kedaluwarsa.']);
        }

        // Check if token is older than 60 minutes
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['email' => 'Token reset kata sandi telah kedaluwarsa. Silakan minta tautan baru.']);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return back()->withErrors(['email' => 'Pengguna dengan email ini tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');
    }
}
