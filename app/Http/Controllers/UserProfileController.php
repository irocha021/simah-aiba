<?php
// app/Http\Controllers\UserProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    /**
     * Exibir página de perfil do usuário
     */
    public function showProfile()
    {
        // Dados mock para estilização
        $user = (object) [
            'first_name' => 'João',
            'last_name' => 'Silva',
            'email' => 'joao.silva@exemplo.com',
            'phone' => '(11) 99999-9999'
        ];

        return view('user.profile', compact('user'));
    }

    /**
     * Atualizar perfil do usuário
     */
    public function updateProfile(Request $request)
    {
        // Validação básica para estilização
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Para estilização, apenas redireciona com mensagem de sucesso
        return redirect()->route('user.profile')
            ->with('success', 'Perfil atualizado com sucesso! (Modo estilização)');
    }

    /**
     * Exibir página de alteração de senha
     */
    public function showPassword()
    {
        return view('user.password');
    }

    /**
     * Atualizar senha do usuário
     */
    public function updatePassword(Request $request)
    {
        // Validação básica para estilização
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
            'new_password_confirmation' => ['required', 'string', 'min:6'],
        ]);

        // Para estilização, apenas redireciona com mensagem de sucesso
        return redirect()->route('user.password')
            ->with('success', 'Senha alterada com sucesso! (Modo estilização)');
    }
}
