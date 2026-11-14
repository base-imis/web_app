<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timout:
    |
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
            'binary'  => env('WKHTML_PDF_BINARY', '/usr/local/bin/wkhtmltopdf'),
            'binary'  => env('WKHTML_IMG_BINARY', '/usr/local/bin/wkhtmltoimage'),

    */


    'pdf' => [
        'enabled' => true,
        'binary' => trim((string) env('WKHTML_PDF_BINARY', PHP_OS_FAMILY === 'Windows'
            ? 'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe'
            : '/usr/bin/wkhtmltopdf'), " \t\n\r\0\x0B\"'"),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
            'orientation'   => 'portrait',
            'encoding'      => 'UTF-8',
            'debug-javascript' => true,
            'enable-javascript' => true,
            'javascript-delay' => 1000,
            'enable-smart-shrinking'=> true,
            'no-stop-slow-scripts' => true,
            'footer-left' =>  'System Generated on ' . date('Y-m-d'),
            'footer-right' => '© Innovative Solution Pvt. Ltd.',
            'footer-font-size' => 8,
            'footer-font-name' => 'Courier'
        ],
        'env'     => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => trim((string) env('WKHTML_IMG_BINARY', PHP_OS_FAMILY === 'Windows'
            ? 'C:/Program Files/wkhtmltopdf/bin/wkhtmltoimage.exe'
            : '/usr/bin/wkhtmltoimage'), " \t\n\r\0\x0B\"'"),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
            'orientation'   => 'portrait',
            'encoding'      => 'UTF-8'
        ],
        'env'     => [],
    ],
];

