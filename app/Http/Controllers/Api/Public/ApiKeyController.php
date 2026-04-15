<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $this->apiKeyService->issueKey($validated['name'], $validated['email']);

            return response()->json([
                'message' => 'Chave de API enviada para o e-mail informado.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao emitir chave de API: ' . $e->getMessage());

            return response()->json([
                'error' => 'Não foi possível processar a solicitação. Tente novamente.',
            ], 500);
        }
    }

    public function showForm(): View
    {
        return view('auth.api-key');
    }

    public function requestKey(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $this->apiKeyService->issueKey($validated['name'], $validated['email']);

            return back()->with('status', 'Chave de API enviada para o e-mail informado!');

        } catch (\Exception $e) {
            \Log::error('Erro ao emitir chave de API: ' . $e->getMessage());
            return back()->with('error', 'Não foi possível processar a solicitação. Tente novamente.');
        }
    }

}
