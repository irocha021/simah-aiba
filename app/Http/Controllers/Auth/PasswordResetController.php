<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            $token = Str::random(64);

            DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));

            Mail::to($user->email)->send(new PasswordResetMail($resetUrl, $user->name));
        }

        return back()->with('status', 'Se o e-mail informado estiver cadastrado, você receberá um link para redefinir sua senha.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token inválido ou expirado.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Token expirado. Solicite uma nova recuperação.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('status', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
    }
}
