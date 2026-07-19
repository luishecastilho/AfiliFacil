<?php

return [

    'import' => [
        'chunk_size' => env('NF_IMPORT_CHUNK_SIZE', 10000),
        'max_file_size_kb' => env('NF_IMPORT_MAX_FILE_SIZE_KB', 51200), // 50 MB
        'allowed_extensions' => ['csv', 'xlsx', 'xls'],
    ],

    'invoice' => [
        'max_retries' => env('NF_INVOICE_MAX_RETRIES', 3),
        'zip_ttl_hours' => env('NF_INVOICE_ZIP_TTL_HOURS', 24),
    ],

    'download' => [
        'presigned_url_ttl_minutes' => env('NF_DOWNLOAD_URL_TTL_MINUTES', 15),
    ],

];
