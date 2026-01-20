<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Exibir formulário de cadastro
     */
    public function showRegisterForm()
    {
        return view('register');
    }

    /**
     * Processar tentativa de cadastro
     */
    public function register(Request $request)
    {
        // Validação dos campos (você pode adicionar lógica de banco depois)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:6'],
            'terms' => ['required', 'accepted'],
        ], [
            'terms.required' => 'Você deve aceitar os termos e condições.',
            'terms.accepted' => 'Você deve aceitar os termos e condições.',
        ]);


        return redirect('/login')->with('success', 'Cadastro realizado com sucesso! Agora você pode fazer login.');
    }
}
