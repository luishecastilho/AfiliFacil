<?php

return [

    'import' => [
        'chunk_size' => env('AFILIFACIL_IMPORT_CHUNK_SIZE', 10000),
        'max_file_size_kb' => env('AFILIFACIL_IMPORT_MAX_FILE_SIZE_KB', 51200), // 50 MB
        'allowed_extensions' => ['csv', 'xlsx', 'xls'],
    ],

    'invoice' => [
        'max_retries' => env('AFILIFACIL_INVOICE_MAX_RETRIES', 3),
        'zip_ttl_hours' => env('AFILIFACIL_INVOICE_ZIP_TTL_HOURS', 24),
        // Active invoice provider: 'null' (fake, dev/test) or 'nacional' (Padrão Nacional NFS-e).
        'driver' => env('AFILIFACIL_INVOICE_DRIVER', 'null'),
    ],

    /*
     | Sistema Nacional NFS-e (Padrão Nacional) — Sefin Nacional / ADN.
     | URLs verificadas em 2026-07 (ver .ai/nfse/pesquisa.md §2).
     */
    'nfse' => [
        'ver_aplic' => env('NFSE_VER_APLIC', 'AfiliFacil-1.0'),
        'timeout' => env('NFSE_HTTP_TIMEOUT', 30),

        'endpoints' => [
            'producao_restrita' => [
                'sefin' => env('NFSE_SEFIN_URL_RESTRITA', 'https://sefin.producaorestrita.nfse.gov.br/SefinNacional'),
                'adn' => env('NFSE_ADN_URL_RESTRITA', 'https://adn.producaorestrita.nfse.gov.br/contribuintes'),
                'danfse' => env('NFSE_DANFSE_URL_RESTRITA', 'https://adn.producaorestrita.nfse.gov.br/danfse'),
                'parametrizacao' => env('NFSE_PARAM_URL_RESTRITA', 'https://adn.producaorestrita.nfse.gov.br/parametrizacao'),
            ],
            'producao' => [
                'sefin' => env('NFSE_SEFIN_URL', 'https://sefin.nfse.gov.br/SefinNacional'),
                'adn' => env('NFSE_ADN_URL', 'https://adn.nfse.gov.br/contribuintes'),
                'danfse' => env('NFSE_DANFSE_URL', 'https://adn.nfse.gov.br/danfse'),
                'parametrizacao' => env('NFSE_PARAM_URL', 'https://adn.nfse.gov.br/parametrizacao'),
            ],
        ],

        // Emissor Nacional web (deep-link para o fluxo manual / Tier B).
        'emissor_nacional_url' => env('NFSE_EMISSOR_URL', 'https://www.nfse.gov.br/EmissorNacional'),
    ],

    'download' => [
        'presigned_url_ttl_minutes' => env('AFILIFACIL_DOWNLOAD_URL_TTL_MINUTES', 15),
    ],

];
