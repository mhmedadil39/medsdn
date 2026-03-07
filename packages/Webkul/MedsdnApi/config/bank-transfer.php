<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bank Transfer API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for bank transfer payment method API integration
    |
    */

    'enabled' => env('BANK_TRANSFER_API_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for bank transfer API endpoints
    |
    */

    'rate_limit' => [
        'upload' => env('BANK_TRANSFER_UPLOAD_RATE_LIMIT', '5,1'), // 5 uploads per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload restrictions
    |
    */

    'upload' => [
        'max_size' => env('BANK_TRANSFER_MAX_FILE_SIZE', 4096), // KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
        'storage_disk' => env('BANK_TRANSFER_STORAGE_DISK', 'private'),
        'storage_path' => 'bank-transfers',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Response Settings
    |--------------------------------------------------------------------------
    |
    | Configure API response behavior
    |
    */

    'response' => [
        'include_order_details' => true,
        'include_reviewer_details' => true,
        'pagination_default' => 15,
        'pagination_max' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security features
    |
    */

    'security' => [
        'require_authentication' => false, // For upload endpoint (uses cart token)
        'validate_mime_type' => true,
        'scan_for_malware' => env('BANK_TRANSFER_SCAN_MALWARE', false),
        'log_all_requests' => env('BANK_TRANSFER_LOG_REQUESTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification behavior
    |
    */

    'notifications' => [
        'send_admin_notification' => true,
        'send_customer_notification' => true,
        'queue_notifications' => true,
    ],
];
