<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // We will send the password reset link to this user.
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email reset password: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'email' => ['Gagal mengirim email reset password. Pastikan konfigurasi SMTP/email valid atau hubungi admin.'],
            ]);
        }

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Link reset password telah berhasil dikirim ke email Anda. Silakan cek kotak masuk atau folder spam.');
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
