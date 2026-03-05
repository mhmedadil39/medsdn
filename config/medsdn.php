<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MedSDN Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains MedSDN-specific configuration options for medical
    | and healthcare eCommerce features.
    |
    */

    'name' => env('APP_NAME', 'MedSDN'),

    'version' => '2.3.13',

    /*
    |--------------------------------------------------------------------------
    | Medical Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific medical and healthcare features.
    |
    */

    'features' => [
        'prescription_required' => env('MEDSDN_PRESCRIPTION_REQUIRED', true),
        'license_verification' => env('MEDSDN_LICENSE_VERIFICATION', true),
        'batch_tracking' => env('MEDSDN_BATCH_TRACKING', true),
        'expiry_management' => env('MEDSDN_EXPIRY_MANAGEMENT', true),
        'drug_interactions' => env('MEDSDN_DRUG_INTERACTIONS', false),
        'cold_chain' => env('MEDSDN_COLD_CHAIN', false),
        'controlled_substances' => env('MEDSDN_CONTROLLED_SUBSTANCES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for healthcare compliance and regulations.
    |
    */

    'compliance' => [
        'hipaa_enabled' => env('MEDSDN_HIPAA_ENABLED', false),
        'fda_tracking' => env('MEDSDN_FDA_TRACKING', false),
        'audit_logging' => env('MEDSDN_AUDIT_LOGGING', true),
        'data_encryption' => env('MEDSDN_DATA_ENCRYPTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prescription Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for prescription upload and verification.
    |
    */

    'prescription' => [
        'upload_enabled' => env('MEDSDN_PRESCRIPTION_UPLOAD', true),
        'verification_required' => env('MEDSDN_PRESCRIPTION_VERIFICATION', true),
        'allowed_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
        'max_file_size' => env('MEDSDN_PRESCRIPTION_MAX_SIZE', 5120), // KB
        'expiry_days' => env('MEDSDN_PRESCRIPTION_EXPIRY_DAYS', 180),
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Settings
    |--------------------------------------------------------------------------
    |
    | Medical product-specific settings.
    |
    */

    'products' => [
        'expiry_alert_days' => env('MEDSDN_EXPIRY_ALERT_DAYS', 90),
        'batch_number_required' => env('MEDSDN_BATCH_REQUIRED', true),
        'manufacturer_required' => env('MEDSDN_MANUFACTURER_REQUIRED', true),
        'storage_conditions_required' => env('MEDSDN_STORAGE_CONDITIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | License Verification
    |--------------------------------------------------------------------------
    |
    | Settings for medical professional license verification.
    |
    */

    'license' => [
        'verification_enabled' => env('MEDSDN_LICENSE_VERIFICATION', true),
        'required_for_checkout' => env('MEDSDN_LICENSE_REQUIRED_CHECKOUT', false),
        'auto_verify' => env('MEDSDN_LICENSE_AUTO_VERIFY', false),
        'expiry_check' => env('MEDSDN_LICENSE_EXPIRY_CHECK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Medical-specific notification settings.
    |
    */

    'notifications' => [
        'expiry_alerts' => env('MEDSDN_EXPIRY_ALERTS', true),
        'prescription_expiry' => env('MEDSDN_PRESCRIPTION_EXPIRY_ALERTS', true),
        'license_expiry' => env('MEDSDN_LICENSE_EXPIRY_ALERTS', true),
        'stock_alerts' => env('MEDSDN_STOCK_ALERTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Third-party integration configurations.
    |
    */

    'integrations' => [
        'ehr_enabled' => env('MEDSDN_EHR_ENABLED', false),
        'ehr_provider' => env('MEDSDN_EHR_PROVIDER', null),
        'pharmacy_system' => env('MEDSDN_PHARMACY_SYSTEM', null),
        'insurance_api' => env('MEDSDN_INSURANCE_API', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting
    |--------------------------------------------------------------------------
    |
    | Medical reporting and analytics settings.
    |
    */

    'reporting' => [
        'controlled_substances_report' => env('MEDSDN_CONTROLLED_REPORT', true),
        'expiry_report' => env('MEDSDN_EXPIRY_REPORT', true),
        'prescription_report' => env('MEDSDN_PRESCRIPTION_REPORT', true),
        'compliance_report' => env('MEDSDN_COMPLIANCE_REPORT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Additional security settings for medical data.
    |
    */

    'security' => [
        'two_factor_auth' => env('MEDSDN_2FA_ENABLED', false),
        'session_timeout' => env('MEDSDN_SESSION_TIMEOUT', 30), // minutes
        'password_expiry_days' => env('MEDSDN_PASSWORD_EXPIRY', 90),
        'ip_whitelist_enabled' => env('MEDSDN_IP_WHITELIST', false),
    ],
];
