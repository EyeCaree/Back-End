<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token,
            ], 200);
        }

        // Jika login gagal
        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('authToken')->plainTextToken;
            
            return response()->json([
                'message' => 'Registrasi berhasil',
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registrasi gagal. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function forgotPassword(Request $request)
    {
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // Buat token reset
    $token = Str::random(64);

    // Simpan token ke tabel password_resets
    DB::table('password_resets')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => Hash::make($token),
            'created_at' => now()
        ]
    );

    // Ambil URL frontend dari .env
        $resetUrl = env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

    // Kirim email reset
    Mail::raw("Klik link berikut untuk reset password Anda:\n\n$resetUrl", function ($message) use ($request) {
        $message->to($request->email);
        $message->subject('Reset Password Eyecare');
    });

    return response()->json([
        'message' => 'Kami telah mengirim link reset password ke email Anda'
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required',
        'password' => 'required|min:6|confirmed',
    ]);

    $reset = DB::table('password_resets')->where('email', $request->email)->first();

    if (!$reset || !Hash::check($request->token, $reset->token)) {
        return response()->json([
            'message' => 'Token tidak valid atau sudah kadaluarsa'
        ], 400);
    }

    $user = User::where('email', $request->email)->first();
    $user->update([
        'password' => Hash::make($request->password)
    ]);

    DB::table('password_resets')->where('email', $request->email)->delete();

    return response()->json([
        'message' => 'Password berhasil diubah'
    ]);
}
}