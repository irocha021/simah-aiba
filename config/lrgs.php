<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LRGS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for LRGS (Local Readout Ground Station) integration.
    | This includes paths to scripts, connection credentials, and temporary
    | file storage locations.
    |
    */

    'paths' => [
        'base' => env('LRGS_BASE_PATH', 'lrgs/bin/getDcpMessages.sh'),
        'config' => env('LRGS_CONFIG_PATH', 'lrgs/MessageBrowser.sc'),
        'temp_directory' => env('LRGS_BASE_TEMPORARY_FILE', 'app/lrgs/temp/'),
    ],

    'credentials' => [
        'host' => env('LRGS_HOST', 'lrgseddn2.cr.usgs.gov'),
        'username' => env('LRGS_USERNAME'),
        'password' => env('LRGS_PASSWORD'),
    ],

    'delimiters' => [
        'before' => '<<<DCPMSG>>>',
        'after' => '<<<DCPMSG>>>' . "\n",
    ],

];
