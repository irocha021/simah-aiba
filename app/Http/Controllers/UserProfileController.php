<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function showProfile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        return redirect()->route('user.profile')
            ->with('success', 'Perfil atualizado com sucesso!');
    }

    public function showPassword()
    {
        return view('user.password');
    }

        public function updatePassword(Request $request)
        {
            $user = Auth::user();

            $rules = [
                'new_password' => ['required', 'string', 'min:6', 'confirmed'],
            ];

            // So exige senha atual se NAO for primeiro login
            if (!$user->must_change_password) {
                $rules['current_password'] = ['required', 'string', 'current_password'];
            }

            $validated = $request->validate($rules);

            $user->update([
                'password' => $validated['new_password'],
                'must_change_password' => false,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);


            return redirect()->route('user.password')
            ->with('success', 'Senha alterada com sucesso! Redirecionando...');

        }

}
