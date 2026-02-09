<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function list()
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->where('role', '!=', 'root')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json(['data' => $users]);
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isRoot()) {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['error' => 'Você não pode excluir a si mesmo.'], 400);
        }

        $user->update([
            'email' => $user->email . '_deleted_' . time(),
            'deleted_by' => $currentUser->id,
        ]);

        $user->delete();

        return response()->json(['success' => 'Usuário excluído com sucesso.']);
    }
}
