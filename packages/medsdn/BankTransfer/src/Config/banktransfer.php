<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure PDF security checks for uploaded payment proofs.
    |
    */

    'strict_pdf_checks' => env('BANKTRANSFER_STRICT_PDF_CHECKS', false),

    'pdf_suspicious_patterns' => [
        // Always check for these dangerous patterns
        '/\/JavaScript/i',      // JavaScript execution
        '/\/JS/i',              // JavaScript (short form)
        '/\/Launch/i',          // Launch actions
        '/\/ImportData/i',      // Import data actions
        '/\/SubmitForm/i',      // Submit form actions
        '/\/GoToR/i',           // Go to remote actions
        '/\/Sound/i',           // Sound objects (can be exploited)
        '/\/Movie/i',           // Movie objects (can be exploited)
        '/\/EmbeddedFile/i',    // Embedded files
        '/\/XFA/i',             // XFA forms (complex, potential security risk)
        '/\/OpenAction/i',      // Auto-execute actions
        '/\/AA/i',              // Additional actions
    ],

    'pdf_strict_patterns' => [
        // Additional patterns checked only when strict mode is enabled
        // These can flag legitimate bank receipts, so they're opt-in
        '/\/Action/i',          // Form actions (common in legitimate PDFs)
        '/\/URI/i',             // External URI references (common in legitimate PDFs)
    ],
];
