<?php

namespace App\Services\DcpMonitor;

use Illuminate\Support\Facades\Http;
use Exception;

class DcpScraperService
{
    private string $baseUrl;
    private int $timeout;
    private string $userAgent;

    public function __construct()
    {
        $this->baseUrl = config('dcp.base_url');
        $this->timeout = config('dcp.request_timeout');
        $this->userAgent = config('dcp.user_agent');
    }

    /**
     * Busca HTML da página principal do DCP
     */
    public function fetchMainPage(string $dcpAddress): string
    {
        $url = $this->baseUrl . 'dcpout.jsp';
        
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($url, [
                    'select_option' => 'dcp_text',
                    'dcp_text' => $dcpAddress,
                    'date_range' => '0'
                ]);

            if (!$response->successful()) {
                throw new Exception("HTTP Error: {$response->status()}");
            }

            // Converte encoding de ISO-8859-1 para UTF-8
            return $this->convertEncoding($response->body());
            
        } catch (\Exception $e) {
            throw new Exception("Erro ao buscar página principal: " . $e->getMessage());
        }
    }

    /**
     * Busca HTML da página de mensagem específica
     */
    public function fetchMessagePage(string $messageFilename): string
    {
        $url = $this->baseUrl . 'msg-html.jsp';
        
        try {
            $response = Http::timeout($this->timeout / 2) // Timeout menor para mensagens
                ->withHeaders($this->getHeaders())
                ->get($url, [
                    'msgfilename' => $messageFilename
                ]);

            if (!$response->successful()) {
                throw new Exception("HTTP Error: {$response->status()}");
            }

            return $this->convertEncoding($response->body());
            
        } catch (\Exception $e) {
            throw new Exception("Erro ao buscar mensagem: " . $e->getMessage());
        }
    }

    /**
     * Headers padrão para as requisições
     */
    private function getHeaders(): array
    {
        return [
            'User-Agent' => $this->userAgent,
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive',
        ];
    }

    /**
     * Converte encoding de ISO-8859-1 para UTF-8
     */
    private function convertEncoding(string $html): string
    {
        return mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Constrói URL completa para um link relativo
     */
    public function buildFullUrl(string $relativePath): string
    {
        return $this->baseUrl . ltrim($relativePath, '/');
    }
}