<?php

namespace App\Services\DcpMonitor;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DcpMonitorService
{
    private DcpScraperService $scraper;
    private DcpParserService $parser;
    private DcpDataTransformer $transformer;

    public function __construct(
        DcpScraperService $scraper,
        DcpParserService $parser,
        DcpDataTransformer $transformer
    ) {
        $this->scraper = $scraper;
        $this->parser = $parser;
        $this->transformer = $transformer;
    }

    /**
     * Busca dados completos do DCP
     */
    public function fetchCompleteData(string $dcpAddress, bool $includeRawData = true): array
    {
        try {
            // Busca HTML da página principal
            $html = $this->scraper->fetchMainPage($dcpAddress);
            
            // Parse dos dados principais
            $data = $this->parser->parseMainPage($html);

            // Se solicitado, enriquece com raw data
            if ($includeRawData) {
                $data['transmissions'] = $this->addRawDataToTransmissions($data['transmissions']);
            }
            
            // Transforma e adiciona metadados
            return $this->transformer->transformCompleteData($data, $dcpAddress);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados DCP completos', [
                'dcp_address' => $dcpAddress,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Busca apenas links das mensagens
     */
    public function fetchMessageLinks(string $dcpAddress, bool $includeRawData = false): array
    {
        try {
            $html = $this->scraper->fetchMainPage($dcpAddress);
            $links = $this->parser->extractMessageLinks($html);
            
            if ($includeRawData) {
                foreach ($links as &$link) {
                    if ($link['message_file']) {
                        $link['raw_data'] = $this->fetchSingleMessageRawData($link['message_file']);
                    }
                }
            }
            
            return [
                'dcp_address' => $dcpAddress,
                'total_links' => count($links),
                'raw_data_included' => $includeRawData,
                'links' => $links
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar links de mensagens', [
                'dcp_address' => $dcpAddress,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Busca raw data de uma única mensagem
     */
    public function fetchSingleMessageRawData(string $messageFilename): array
    {
        try {
            $html = $this->scraper->fetchMessagePage($messageFilename);
            return $this->parser->parseMessagePage($html);
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar raw data da mensagem', [
                'message_filename' => $messageFilename,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enriquece transmissões com raw data
     */
    private function addRawDataToTransmissions(array $transmissions): array
    {
        $enriched = [];
        
        foreach ($transmissions as $transmission) {
            if (isset($transmission['message_link']) && $transmission['has_message']) {
                try {
                    // Extrai filename do link
                    preg_match('/msgfilename=([^&]+)/', $transmission['message_link'], $matches);
                    $messageFile = $matches[1] ?? null;
                    
                    if ($messageFile) {
                        $rawDataInfo = $this->fetchSingleMessageRawData($messageFile);
                        $transmission['raw_data'] = $rawDataInfo['raw_data'];
                        //cd $transmission['raw_data_parsed'] = $this->parser->parseRawData($rawDataInfo['raw_data']);
                        $transmission['message_parameters'] = $rawDataInfo['parameters'] ?? [];
                    }
                } catch (\Exception $e) {
                    $transmission['raw_data'] = null;
                    $transmission['raw_data_error'] = $e->getMessage();
                }
            } else {
                $transmission['raw_data'] = null;
                //$transmission['raw_data_parsed'] = null;
            }
            
            $enriched[] = $transmission;
        }
        
        return $enriched;
    }
}