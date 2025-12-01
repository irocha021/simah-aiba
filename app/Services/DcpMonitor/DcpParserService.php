<?php

namespace App\Services\DcpMonitor;

use DOMDocument;
use DOMXPath;
use DOMElement;

class DcpParserService
{
    /**
     * Parse completo da página principal
     */
    public function parseMainPage(string $html): array
    {
        $xpath = $this->createXPath($html);
        
        return [
            'metadata' => $this->extractMetadata($xpath),
            'transmissions' => $this->extractTransmissions($xpath),
            'utc_time' => $this->extractUtcTime($xpath),
        ];
    }

    /**
     * Parse da página de mensagem
     */
    public function parseMessagePage(string $html): array
    {
        $xpath = $this->createXPath($html);
        
        // Extrai o raw data
        $rawData = $this->extractRawData($xpath);
        
        // Extrai parâmetros da mensagem
        $parameters = $this->extractMessageParameters($xpath);
        
        // Extrai informações do header
        $headerInfo = $this->extractMessageHeader($xpath);
        
        return [
            'raw_data' => $rawData,
            'parameters' => $parameters,
            'header_info' => $headerInfo
        ];
    }

    /**
     * Extrai apenas os links das mensagens
     */
    public function extractMessageLinks(string $html): array
    {
        $xpath = $this->createXPath($html);
        
        $links = [];
        $linkNodes = $xpath->query("//tr[@class='full_perf_report']//td[@class='time']//a");
        
        foreach ($linkNodes as $link) {
            $href = $link->getAttribute('href');
            $time = trim($link->textContent);
            
            preg_match('/msgfilename=([^&]+)/', $href, $matches);
            $messageFile = $matches[1] ?? null;
            
            $links[] = [
                'time' => $time,
                'relative_url' => $href,
                'full_url' => config('dcp.base_url') . $href,
                'message_file' => $messageFile
            ];
        }
        
        return $links;
    }

    /**
     * Parse do raw data em campos estruturados
     */
    public function parseRawData(?string $rawData): ?array
    {
        if (!$rawData) {
            return null;
        }
        
        $parsed = [
            'original' => $rawData,
            'fields' => []
        ];
        
        // Separa header dos dados usando >>>
        if (strpos($rawData, '>>>') !== false) {
            list($header, $data) = explode('>>>', $rawData, 2);
            $parsed['header'] = trim($header);
            
            // Parse dos valores separados por ponto e vírgula
            $values = array_map('trim', explode(';', trim($data)));
            $parsed['values'] = $values;
            
            // Mapeia campos conhecidos
            $parsed['fields'] = $this->mapKnownFields($values);
            
            // Identifica modelo do equipamento
            if (preg_match('/CR\d+/', $rawData, $matches)) {
                $parsed['equipment_model'] = $matches[0];
            }
            
            // Identifica nome da estação se presente
            if (preg_match('/DBHIDRO_([^;]+)/', $rawData, $matches)) {
                $parsed['station_type'] = trim($matches[1]);
            }
        } else {
            $parsed['raw_values'] = $rawData;
        }
        
        return $parsed;
    }

    /**
     * Cria XPath a partir do HTML
     */
    private function createXPath(string $html): DOMXPath
    {
        libxml_use_internal_errors(true);
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        libxml_clear_errors();
        
        return new DOMXPath($dom);
    }

    /**
     * Extrai metadados do cabeçalho
     */
    private function extractMetadata(DOMXPath $xpath): array
    {
        $metadata = [];
        $rows = $xpath->query("//table[@class='headerTable']//tr");
        
        $mapping = [
            'DCP Address:' => 'dcp_address',
            'First transmission time:' => 'first_transmission_time',
            'Self-timed channel:' => 'channel',
            'Transmission interval:' => 'transmission_interval',
            'Transmission window:' => 'transmission_window',
            'Preamble:' => 'preamble',
            'Baud rate:' => 'baud_rate'
        ];
        
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length >= 2) {
                $key = trim($cells->item(0)->textContent);
                $value = trim($cells->item(1)->textContent);
                
                if (isset($mapping[$key])) {
                    $metadata[$mapping[$key]] = $value;
                }
            }
        }
        
        return $metadata;
    }

    /**
     * Extrai dados das transmissões
     */
    private function extractTransmissions(DOMXPath $xpath): array
    {
        $transmissions = [];
        $rows = $xpath->query("//tr[@class='full_perf_report']");
        
        foreach ($rows as $index => $row) {
            $cells = $row->getElementsByTagName('td');
            
            if ($cells->length >= 13) {
                $transmission = [
                    'index' => $index,
                    'channel' => trim($cells->item(0)->textContent),
                    'date' => trim($cells->item(1)->textContent),
                    'transmit_start' => $this->extractTransmitTime($cells->item(2)),
                    'transmit_end' => $this->extractTransmitTime($cells->item(3)),
                    'window_start' => trim($cells->item(4)->textContent),
                    'window_end' => trim($cells->item(5)->textContent),
                    'failure_code' => trim($cells->item(6)->textContent),
                    'signal_strength' => $this->parseNumericValue($cells->item(7)->textContent),
                    'message_length' => $this->parseNumericValue($cells->item(8)->textContent),
                    'frequency_offset' => trim($cells->item(9)->textContent),
                    'modulation_index' => trim($cells->item(10)->textContent),
                    'drgs_code' => trim($cells->item(11)->textContent),
                    'battery_voltage' => trim($cells->item(12)->textContent),
                ];
                
                $transmission['is_successful'] = $transmission['failure_code'] === 'G';
                $transmission['is_missing'] = $transmission['failure_code'] === 'M';
                
                $link = $this->extractMessageLink($cells->item(2));
                if ($link) {
                    $transmission['message_link'] = $link;
                    $transmission['has_message'] = true;
                } else {
                    $transmission['has_message'] = false;
                }
                
                $transmissions[] = $transmission;
            }
        }
        
        return $transmissions;
    }

    /**
     * Extrai tempo de transmissão
     */
    private function extractTransmitTime(DOMElement $cell): array
    {
        $xpath = new DOMXPath($cell->ownerDocument);
        $alarmSpan = $xpath->query(".//span[@class='alarm']", $cell);
        $hasAlarm = $alarmSpan->length > 0;
        $time = trim($cell->textContent);
        
        return [
            'time' => $time,
            'has_alarm' => $hasAlarm,
            'is_missing' => $time === '--:--:--'
        ];
    }

    /**
     * Extrai link da mensagem
     */
    private function extractMessageLink(DOMElement $cell): ?string
    {
        $links = $cell->getElementsByTagName('a');
        
        if ($links->length > 0) {
            $href = $links->item(0)->getAttribute('href');
            return config('dcp.base_url') . $href;
        }
        
        return null;
    }

    /**
     * Extrai raw data da página de mensagem
     */
    private function extractRawData(DOMXPath $xpath): ?string
    {
        $preElements = $xpath->query("//pre");
        
        if ($preElements->length > 0) {
            $rawData = trim($preElements->item(0)->textContent);
            $rawData = html_entity_decode($rawData);
            return preg_replace('/\s+/', ' ', $rawData);
        }
        
        return null;
    }

    /**
     * Extrai parâmetros da mensagem
     */
    private function extractMessageParameters(DOMXPath $xpath): array
    {
        $parameters = [];
        $cells = $xpath->query("//h3[contains(text(), 'Message Parameters')]/following-sibling::table[1]//td");
        
        $mapping = [
            'DCP Address' => 'dcp_address',
            'Message Quality' => 'message_quality',
            'Signal Strength' => 'signal_strength',
            'Frequency Offset' => 'frequency_offset',
            'GOES Channel' => 'goes_channel',
            'Message Length' => 'message_length',
            'DRGS code' => 'drgs_code',
            'DRGS Description' => 'drgs_description',
            'Carrier Start (UTC)' => 'carrier_start_utc',
            'Carrier Stop (UTC)' => 'carrier_stop_utc',
            'Additional Flags' => 'additional_flags'
        ];
        
        foreach ($cells as $cell) {
            $text = trim($cell->textContent);
            if (strpos($text, ':') !== false) {
                list($key, $value) = explode(':', $text, 2);
                $key = trim($key);
                $value = trim($value);
                
                if (isset($mapping[$key])) {
                    $parameters[$mapping[$key]] = $value;
                }
            }
        }
        
        return $parameters;
    }

    /**
     * Extrai header da mensagem
     */
    private function extractMessageHeader(DOMXPath $xpath): array
    {
        $headerInfo = [];
        $h2Elements = $xpath->query("//h2");
        
        if ($h2Elements->length > 0) {
            $headerText = $h2Elements->item(0)->textContent;
            
            if (preg_match('/([A-F0-9]{8})\s*-\s*(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})\s+UTC/', $headerText, $matches)) {
                $headerInfo['dcp_address'] = $matches[1];
                $headerInfo['timestamp_utc'] = $matches[2];
            }
            
            $lines = explode("\n", $headerText);
            if (count($lines) > 1) {
                $secondLine = trim($lines[1]);
                if (preg_match('/^([^,]+)/', $secondLine, $matches)) {
                    $headerInfo['station_name'] = trim($matches[1]);
                }
            }
        }
        
        return $headerInfo;
    }

    /**
     * Extrai tempo UTC
     */
    private function extractUtcTime(DOMXPath $xpath): ?string
    {
        $divs = $xpath->query("//div[contains(text(), 'UTC:')]");
        
        if ($divs->length > 0) {
            $text = $divs->item(0)->textContent;
            preg_match('/UTC:\s*(.+)/', $text, $matches);
            return isset($matches[1]) ? trim($matches[1]) : null;
        }
        
        return null;
    }

    /**
     * Converte valor para numérico
     */
    private function parseNumericValue(string $value): ?int
    {
        $value = trim($value);
        
        if (in_array($value, ['N/A', '--', ''])) {
            return null;
        }
        
        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * Mapeia campos conhecidos do raw data
     */
    private function mapKnownFields(array $values): array
    {
        // Estrutura comum dos dados DCP
        // Os índices podem variar dependendo da configuração da estação
        return [
            'station_id' => $values[0] ?? null,
            'measurements' => array_slice($values, 1, 16),
            'temperature' => isset($values[17]) && is_numeric($values[17]) ? (float)$values[17] : null,
            'humidity' => isset($values[18]) && is_numeric($values[18]) ? (float)$values[18] : null,
            'wind_speed' => isset($values[19]) && is_numeric($values[19]) ? (float)$values[19] : null,
            'pressure' => isset($values[23]) && is_numeric($values[23]) ? (float)$values[23] : null,
            'latitude' => isset($values[24]) && is_numeric($values[24]) ? (float)$values[24] : null,
            'longitude' => isset($values[25]) && is_numeric($values[25]) ? (float)$values[25] : null,
            'battery_voltage' => isset($values[30]) && is_numeric($values[30]) ? (float)$values[30] : null,
        ];
    }
}