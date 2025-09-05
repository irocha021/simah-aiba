<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DCP Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para o sistema de monitoramento DCP
    |
    */

    'base_url' => env('DCP_BASE_URL', 'https://www.sutronwin.com/dcpmon/'),
    
    'default_address' => env('DCP_DEFAULT_ADDRESS', 'b04041e0'),
    
    'request_timeout' => env('DCP_REQUEST_TIMEOUT', 30),
    
    'user_agent' => env('DCP_USER_AGENT', 'Mozilla/5.0 (compatible; Laravel DCP Monitor)'),
    
    'auto_fetch_raw_data' => env('DCP_AUTO_FETCH_RAW_DATA', true),
];