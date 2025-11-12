<?php

namespace App\Services\API_Hidroweb;

use Illuminate\Support\Facades\Http;

class BaseHidroWebService
{
    protected string $baseUrl;
    protected ?string $token = null;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->baseUrl = config('services.hidroweb.base_url');
        $this->authService = $authService;
    }

    /**
     * Ensure the service is authenticated.
     *
     * @throws \Exception
     */
    protected function ensureAuthenticated(): void
    {
        if ($this->token === null) {
            $this->authenticate();
        }
    }

    /**
     * Authenticate and set the token.
     *
     * @throws \Exception
     */
    public function authenticate(): void
    {
        $token = $this->authService->authenticate();

        if ($token) {
            $this->setToken($token);
        } else {
            throw new \Exception('Failed to authenticate with HidroWeb API');
        }
    }

    /**
     * Set the authentication token.
     *
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Execute a GET request to the API.
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return array
     */
    protected function get(string $endpoint, array $queryParams = []): array
    {
        $this->ensureAuthenticated(); // Garante que o token está disponível

        $url = "{$this->baseUrl}/{$endpoint}";

        try {
            $response = Http::timeout(60) // Aumenta timeout para 60 segundos
                ->withHeaders([
                    'Authorization' => "Bearer {$this->token}",
                    'accept' => '*/*',
                ])->get($url, $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            // Se receber 401, o token pode ter expirado - invalidar para forçar nova autenticação
            if ($response->status() === 401) {
                $this->token = null;
            }

            throw new \Exception("API Error: {$response->status()} - {$response->body()}");
        } catch (\Exception $e) {
            report($e);
            return ['error' => $e->getMessage()];
        }
    }
}
