<?php

namespace App\Services\API_Hidroweb;

use Illuminate\Support\Facades\Http;

class AuthService
{
    private string $baseUrl;
    private string $identifier;
    private string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.hidroweb.base_url');
        $this->identifier = config('services.hidroweb.identifier');
        $this->password = config('services.hidroweb.password');
    }

    /**
     * Authenticate and get the token.
     *
     * @return string|null
     */
    public function authenticate(): ?string
    {
        $endpoint = 'EstacoesTelemetricas/OAUth/v1';

        try {
            $response = Http::withHeaders([
                'accept' => '*/*',
                'Identificador' => $this->identifier,
                'Senha' => $this->password,
            ])->get("{$this->baseUrl}/{$endpoint}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['items']['tokenautenticacao'] ?? null;
            }

            throw new \Exception("Login failed: {$response->status()} - {$response->body()}");
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }
}
