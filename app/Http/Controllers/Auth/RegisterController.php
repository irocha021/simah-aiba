<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeNewAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ], [
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
        ]);

        $plainPassword = Str::random(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $plainPassword,
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        Mail::to($user->email)->send(new WelcomeNewAdmin($user, $plainPassword));

        return redirect()->route('register')
            ->with('success', 'Administrador cadastrado com sucesso! Um e-mail com as credenciais foi enviado para ' . $user->email);
    }
}
