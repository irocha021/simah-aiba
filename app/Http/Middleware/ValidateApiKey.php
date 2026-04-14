<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainKey = $request->header('X-Api-Key')
            ?? $request->query('api_key');

        if (! $plainKey) {
            return response()->json([
                'error' => 'Chave de API não fornecida.',
            ], 401);
        }

        $apiKey = ApiKey::findByPlainKey($plainKey);

        if (! $apiKey) {
            return response()->json([
                'error' => 'Chave de API inválida ou inativa.',
            ], 401);
        }

        $request->attributes->set('api_key', $apiKey);

        $apiKey->updateQuietly(['last_used_at' => now()]);

        return $next($request);
    }
}
