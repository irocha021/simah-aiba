<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);
       
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->must_change_password) {
                return redirect()->route('user.password')
                    ->with('info', 'Você precisa alterar sua senha antes de continuar.');
            }

            return redirect()->intended('/')->with('success', 'Login realizado com sucesso!');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate(); 
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
