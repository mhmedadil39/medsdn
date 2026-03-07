<?php

namespace Webkul\BankTransfer\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Allowed file types.
     *
     * @var array
     */
    protected const ALLOWED_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    /**
     * Allowed file extensions.
     *
     * @var array
     */
    protected const ALLOWED_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'pdf',
    ];

    /**
     * Maximum file size in bytes (4MB).
     *
     * @var int
     */
    protected const MAX_FILE_SIZE = 4 * 1024 * 1024;

    /**
     * Validate uploaded file.
     *
     * Performs comprehensive security validation including:
     * - File validity check
     * - Size limit enforcement (4MB)
     * - MIME type validation using finfo_file()
     * - Extension whitelist enforcement
     * - Double extension check
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public static function validate(UploadedFile $file): array
    {
        // Check if file is valid
        if (! $file->isValid()) {
            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file'),
            ];
        }

        // Validate file size (4MB maximum)
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.file-too-large', [
                    'size' => '4MB',
                ]),
            ];
        }

        // Validate MIME type using finfo (server-side, not client-provided)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        if (! in_array($mimeType, self::ALLOWED_TYPES)) {
            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file-type'),
            ];
        }

        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file-extension'),
            ];
        }

        // Check for double extensions (e.g., file.php.jpg)
        $filename = $file->getClientOriginalName();
        $parts = explode('.', $filename);
        
        if (count($parts) > 2) {
            // Check if any part before the last extension is a dangerous extension
            $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'exe', 'sh', 'bat', 'cmd', 'com', 'js', 'jar'];
            
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if (in_array(strtolower($parts[$i]), $dangerousExtensions)) {
                    return [
                        'valid' => false,
                        'error' => trans('banktransfer::app.shop.errors.invalid-file-extension'),
                    ];
                }
            }
        }

        // Additional check: Ensure MIME type matches extension
        $expectedMimes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'pdf' => ['application/pdf'],
        ];

        if (isset($expectedMimes[$extension]) && ! in_array($mimeType, $expectedMimes[$extension])) {
            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.mime-extension-mismatch'),
            ];
        }

        // Content-based validation for images
        if (in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])) {
            $imageValidation = self::validateImageContent($file->getRealPath());
            
            if (! $imageValidation['valid']) {
                return $imageValidation;
            }
        }

        // Content-based validation for PDFs
        if ($mimeType === 'application/pdf') {
            $pdfValidation = self::validatePdfContent($file->getRealPath());
            
            if (! $pdfValidation['valid']) {
                return $pdfValidation;
            }
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Validate image file content to ensure it's a legitimate image.
     *
     * Uses getimagesize() to verify the file is actually an image
     * and not a malicious file with an image extension.
     *
     * @param  string  $filePath
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected static function validateImageContent(string $filePath): array
    {
        try {
            // getimagesize returns false if file is not a valid image
            $imageInfo = @getimagesize($filePath);

            if ($imageInfo === false) {
                \Log::warning('Bank Transfer image validation failed - not a valid image', [
                    'file_path' => basename($filePath),
                ]);

                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.invalid-image-content'),
                ];
            }

            // Additional check: Verify image dimensions are reasonable
            // Prevent extremely large images that could cause memory issues
            $maxDimension = 10000; // 10000 pixels max width or height
            
            if ($imageInfo[0] > $maxDimension || $imageInfo[1] > $maxDimension) {
                \Log::warning('Bank Transfer image validation failed - dimensions too large', [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'max_dimension' => $maxDimension,
                ]);

                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.image-dimensions-too-large'),
                ];
            }

            // Enhanced security: Check for embedded scripts or malicious content
            $securityCheck = self::checkImageSecurity($filePath, $imageInfo);
            if (!$securityCheck['valid']) {
                return $securityCheck;
            }

            return [
                'valid' => true,
                'error' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Bank Transfer image validation exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file'),
            ];
        }
    }

    /**
     * Enhanced security check for image files.
     *
     * Performs additional security validation to detect:
     * - Embedded scripts or malicious content
     * - Suspicious file headers
     * - Polyglot files (files that are valid in multiple formats)
     *
     * @param  string  $filePath
     * @param  array  $imageInfo
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected static function checkImageSecurity(string $filePath, array $imageInfo): array
    {
        try {
            // Read first 1KB of file to check for suspicious content
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.invalid-file'),
                ];
            }

            $header = fread($handle, 1024);
            fclose($handle);

            // Check for embedded scripts or suspicious patterns
            $suspiciousPatterns = [
                '/<\?php/i',           // PHP tags
                '/<script/i',          // JavaScript
                '/javascript:/i',      // JavaScript protocol
                '/vbscript:/i',        // VBScript
                '/data:/i',            // Data URLs
                '/eval\s*\(/i',        // eval() calls
                '/base64_decode/i',    // Base64 decode
                '/exec\s*\(/i',        // exec() calls
                '/system\s*\(/i',      // system() calls
                '/shell_exec/i',       // shell_exec
                '/passthru/i',         // passthru
                '/file_get_contents/i', // file_get_contents
                '/fopen\s*\(/i',       // fopen calls
                '/curl_exec/i',        // curl_exec
                '/\x00/i',             // Null bytes
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $header)) {
                    \Log::warning('Bank Transfer image security check failed - suspicious content detected', [
                        'file_path' => basename($filePath),
                        'pattern' => $pattern,
                        'ip_address' => request()->ip(),
                    ]);

                    return [
                        'valid' => false,
                        'error' => trans('banktransfer::app.shop.errors.suspicious-content-detected'),
                    ];
                }
            }

            // Validate image format-specific headers
            $formatValidation = self::validateImageFormatHeaders($filePath, $imageInfo);
            if (!$formatValidation['valid']) {
                return $formatValidation;
            }

            return [
                'valid' => true,
                'error' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Bank Transfer image security check exception', [
                'error' => $e->getMessage(),
                'file_path' => basename($filePath),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file'),
            ];
        }
    }

    /**
     * Validate image format-specific headers.
     *
     * Ensures the file headers match the expected format signatures.
     *
     * @param  string  $filePath
     * @param  array  $imageInfo
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected static function validateImageFormatHeaders(string $filePath, array $imageInfo): array
    {
        try {
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.invalid-file'),
                ];
            }

            $header = fread($handle, 20); // Read first 20 bytes
            fclose($handle);

            $imageType = $imageInfo[2]; // IMAGETYPE_* constant

            // Validate format-specific signatures
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    // JPEG files start with FF D8 FF
                    if (substr($header, 0, 3) !== "\xFF\xD8\xFF") {
                        \Log::warning('Bank Transfer JPEG header validation failed', [
                            'file_path' => basename($filePath),
                            'expected' => 'FF D8 FF',
                            'actual' => bin2hex(substr($header, 0, 3)),
                        ]);

                        return [
                            'valid' => false,
                            'error' => trans('banktransfer::app.shop.errors.invalid-jpeg-header'),
                        ];
                    }
                    break;

                case IMAGETYPE_PNG:
                    // PNG files start with 89 50 4E 47 0D 0A 1A 0A
                    $pngSignature = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
                    if (substr($header, 0, 8) !== $pngSignature) {
                        \Log::warning('Bank Transfer PNG header validation failed', [
                            'file_path' => basename($filePath),
                            'expected' => bin2hex($pngSignature),
                            'actual' => bin2hex(substr($header, 0, 8)),
                        ]);

                        return [
                            'valid' => false,
                            'error' => trans('banktransfer::app.shop.errors.invalid-png-header'),
                        ];
                    }
                    break;

                case IMAGETYPE_WEBP:
                    // WebP files start with RIFF....WEBP
                    if (substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WEBP') {
                        \Log::warning('Bank Transfer WebP header validation failed', [
                            'file_path' => basename($filePath),
                            'header' => bin2hex($header),
                        ]);

                        return [
                            'valid' => false,
                            'error' => trans('banktransfer::app.shop.errors.invalid-webp-header'),
                        ];
                    }
                    break;
            }

            return [
                'valid' => true,
                'error' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Bank Transfer image format validation exception', [
                'error' => $e->getMessage(),
                'file_path' => basename($filePath),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file'),
            ];
        }
    }

    /**
     * Validate PDF file content to ensure it's a legitimate PDF.
     *
     * Checks PDF file signature (magic bytes) and performs additional
     * security validation to detect malicious content.
     *
     * @param  string  $filePath
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected static function validatePdfContent(string $filePath): array
    {
        try {
            // Read first 1KB to check PDF signature and content
            $handle = fopen($filePath, 'rb');
            
            if ($handle === false) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.invalid-file'),
                ];
            }

            $header = fread($handle, 1024);
            fclose($handle);

            // Valid PDF files start with %PDF-
            if (substr($header, 0, 5) !== '%PDF-') {
                \Log::warning('Bank Transfer PDF validation failed - invalid PDF signature', [
                    'file_path' => basename($filePath),
                    'header' => bin2hex(substr($header, 0, 10)),
                ]);

                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.invalid-pdf-content'),
                ];
            }

            // Enhanced security: Check for suspicious PDF content
            $securityCheck = self::checkPdfSecurity($header, $filePath);
            if (!$securityCheck['valid']) {
                return $securityCheck;
            }

            // Validate PDF version (should be 1.0 to 2.0)
            if (preg_match('/%PDF-(\d+\.\d+)/', $header, $matches)) {
                $version = (float) $matches[1];
                if ($version < 1.0 || $version > 2.0) {
                    \Log::warning('Bank Transfer PDF validation failed - unsupported version', [
                        'file_path' => basename($filePath),
                        'version' => $version,
                    ]);

                    return [
                        'valid' => false,
                        'error' => trans('banktransfer::app.shop.errors.unsupported-pdf-version'),
                    ];
                }
            }

            return [
                'valid' => true,
                'error' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Bank Transfer PDF validation exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file'),
            ];
        }
    }

    /**
     * Enhanced security check for PDF files.
     *
     * Detects potentially malicious PDF content including:
     * - JavaScript execution
     * - Form actions
     * - External references
     * - Suspicious objects
     *
     * @param  string  $header
     * @param  string  $filePath
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected static function checkPdfSecurity(string $header, string $filePath): array
    {
        try {
            // Get suspicious patterns from config
            $suspiciousPatterns = config('banktransfer.pdf_suspicious_patterns', [
                '/\/JavaScript/i',
                '/\/JS/i',
                '/\/Launch/i',
                '/\/ImportData/i',
                '/\/SubmitForm/i',
                '/\/GoToR/i',
                '/\/Sound/i',
                '/\/Movie/i',
                '/\/EmbeddedFile/i',
                '/\/XFA/i',
                '/\/OpenAction/i',
                '/\/AA/i',
            ]);

            // Add strict patterns if strict mode is enabled
            if (config('banktransfer.strict_pdf_checks', false)) {
                $strictPatterns = config('banktransfer.pdf_strict_patterns', [
                    '/\/Action/i',
                    '/\/URI/i',
                ]);
                $suspiciousPatterns = array_merge($suspiciousPatterns, $strictPatterns);
            }

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $header)) {
                    \Log::warning('Bank Transfer PDF security check failed - suspicious content detected', [
                        'file_path' => basename($filePath),
                        'pattern' => $pattern,
                        'ip_address' => request()->ip(),
                    ]);

                    return [
                        'valid' => false,
                        'error' => trans('banktransfer::app.shop.errors.suspicious-pdf-content'),
                    ];
                }
            }

            // Check for excessive object references (potential DoS)
            $objectCount = preg_match_all('/\d+\s+\d+\s+obj/', $header);
            if ($objectCount > 50) { // Reasonable limit for payment receipts
                \Log::warning('Bank Transfer PDF validation failed - too many objects', [
                    'file_path' => basename($filePath),
                    'object_count' => $objectCount,
                ]);

                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.complex-pdf-structure'),
                ];
            }

            return [
                'valid' => true,
                'error' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Bank Transfer PDF security check exception', [
                'error' => $e->getMessage(),
                'file_path' => basename($filePath),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.invalid-file'),
            ];
        }
    }

    /**
     * Sanitize filename to prevent security issues.
     *
     * Removes path traversal attempts, null bytes, special characters,
     * and ensures the filename is safe for storage.
     *
     * Security measures:
     * - Removes null bytes (security vulnerability)
     * - Prevents path traversal (../, ..\, /, \)
     * - Removes control characters
     * - Removes special characters
     * - Prevents multiple consecutive dots
     * - Prevents hidden files (leading dots)
     * - Limits filename length
     *
     * @param  string  $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove null bytes (security vulnerability)
        $filename = str_replace("\0", '', $filename);
        
        // Remove path traversal attempts (../, ..\, /, \)
        $filename = str_replace(['../', '..\\', '..', '/', '\\'], '', $filename);
        
        // Remove control characters (ASCII 0-31 and 127)
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
        
        // Remove special characters that could cause issues
        // Keep only alphanumeric, dots, dashes, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple consecutive dots (could be used for obfuscation)
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        
        // Double-extension validation: Keep only the final extension
        // This prevents attacks like "malicious.php.jpg"
        $parts = explode('.', $filename);
        if (count($parts) > 2) {
            $basename = $parts[0];
            $extension = end($parts);
            $filename = $basename . '.' . $extension;
        }
        
        // Remove leading/trailing dots and spaces
        $filename = trim($filename, '. ');

        // Ensure filename doesn't start with a dot (hidden files)
        if (str_starts_with($filename, '.')) {
            $filename = substr($filename, 1);
        }

        // Check for suspicious patterns
        if (self::hasSuspiciousPattern($filename)) {
            \Log::warning('Bank Transfer suspicious filename pattern detected', [
                'filename' => $filename,
                'ip_address' => request()->ip(),
            ]);
            
            // Replace with generic name if suspicious
            $filename = 'payment_proof';
        }

        // Ensure filename is not empty after sanitization
        if (empty($filename) || $filename === '.') {
            $filename = 'payment_proof';
        }

        // Limit filename length to prevent filesystem issues
        if (strlen($filename) > 100) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $name = substr($name, 0, 100 - strlen($extension) - 1);
            $filename = $name.'.'.$extension;
        }

        return $filename;
    }

    /**
     * Check for suspicious patterns in filename.
     *
     * Detects patterns commonly used in attacks:
     * - Executable extensions
     * - Script extensions
     * - System file patterns
     * - Encoded characters
     * - Unicode tricks
     * - Path traversal attempts
     *
     * @param  string  $filename
     * @return bool
     */
    protected static function hasSuspiciousPattern(string $filename): bool
    {
        $suspiciousPatterns = [
            // Executable extensions
            '/\.(exe|bat|cmd|com|pif|scr|vbs|js|jar|msi|app|deb|rpm|dmg)$/i',
            // Script extensions
            '/\.(php|phtml|php3|php4|php5|phar|asp|aspx|jsp|cgi|pl|py|rb|sh)$/i',
            // System files
            '/^(htaccess|htpasswd|web\.config|\.env|\.git|\.svn)$/i',
            // Encoded characters (URL encoding, hex encoding)
            '/%[0-9a-f]{2}/i',
            '/\\x[0-9a-f]{2}/i',
            // Unicode tricks (right-to-left override, etc.)
            '/[\x{202E}\x{200E}\x{200F}\x{061C}\x{2066}\x{2067}\x{2068}\x{2069}]/u',
            // Path traversal attempts
            '/\.\.[\/\\]/i',
            '/\.\.\./i',
            // Null bytes and control characters
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/i',
            // Suspicious file patterns
            '/^(con|prn|aux|nul|com[1-9]|lpt[1-9])(\.|$)/i', // Windows reserved names
            '/^\./i', // Hidden files
            // Double extensions with dangerous combinations
            '/\.(php|asp|jsp|cgi|pl|py|rb|sh)\.(jpg|jpeg|png|gif|pdf|txt)$/i',
            // Polyglot file indicators
            '/GIF89a.*<\?php/i',
            '/\xFF\xD8\xFF.*<\?php/i', // JPEG with PHP
            // Suspicious length patterns
            '/^.{200,}$/', // Extremely long filenames
            '/^.{1,2}$/', // Extremely short filenames (except normal extensions)
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        // Additional checks for suspicious character combinations
        if (self::hasAdvancedSuspiciousPatterns($filename)) {
            return true;
        }

        return false;
    }

    /**
     * Advanced suspicious pattern detection.
     *
     * Performs more sophisticated checks for malicious filename patterns.
     *
     * @param  string  $filename
     * @return bool
     */
    protected static function hasAdvancedSuspiciousPatterns(string $filename): bool
    {
        // Check for homograph attacks (similar-looking characters)
        $homographs = [
            'а' => 'a', 'е' => 'e', 'о' => 'o', 'р' => 'p', 'с' => 'c', 'х' => 'x', // Cyrillic
            'ο' => 'o', 'α' => 'a', 'ρ' => 'p', 'ε' => 'e', // Greek
        ];

        foreach ($homographs as $fake => $real) {
            if (strpos($filename, $fake) !== false) {
                \Log::warning('Bank Transfer filename homograph attack detected', [
                    'filename' => $filename,
                    'suspicious_char' => $fake,
                    'ip_address' => request()->ip(),
                ]);
                return true;
            }
        }

        // Check for excessive repetition (potential DoS)
        if (preg_match('/(.)\1{20,}/', $filename)) {
            return true;
        }

        // Check for mixed scripts (potential confusion attack)
        $hasLatin = preg_match('/[a-zA-Z]/', $filename);
        $hasCyrillic = preg_match('/[\x{0400}-\x{04FF}]/u', $filename);
        $hasArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $filename);
        $hasGreek = preg_match('/[\x{0370}-\x{03FF}]/u', $filename);

        $scriptCount = (int)$hasLatin + (int)$hasCyrillic + (int)$hasArabic + (int)$hasGreek;
        if ($scriptCount > 1) {
            \Log::warning('Bank Transfer filename mixed script attack detected', [
                'filename' => $filename,
                'scripts' => compact('hasLatin', 'hasCyrillic', 'hasArabic', 'hasGreek'),
                'ip_address' => request()->ip(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Store uploaded file securely.
     *
     * Security measures:
     * - Files stored in private disk (storage/app/private) outside public web root
     * - Comprehensive validation before storage
     * - Sanitized and unique filenames to prevent collisions and attacks
     * - Files organized by order ID for easy management
     * - Detailed security logging for audit trails
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  int  $orderId
     * @return string|false File path on success, false on failure
     */
    public static function store(UploadedFile $file, int $orderId)
    {
        try {
            // Log upload attempt for security audit
            \Log::info('Bank Transfer file upload initiated', [
                'order_id' => $orderId,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Validate file first (MIME type, size, extension, etc.)
            $validation = self::validate($file);

            if (! $validation['valid']) {
                \Log::warning('Bank Transfer file validation failed', [
                    'error' => $validation['error'],
                    'order_id' => $orderId,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'ip_address' => request()->ip(),
                ]);
                
                return false;
            }

            // Generate unique filename with sanitization
            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = self::sanitizeFilename($originalName);
            
            // Add timestamp and random string to ensure uniqueness
            $uniqueName = $sanitizedName.'_'.time().'_'.Str::random(8).'.'.$extension;

            // Store file in private storage (storage/app/private/bank-transfers/{order_id}/)
            // This location is NOT accessible via direct URL for security
            $directory = 'bank-transfers/'.$orderId;
            $path = $file->storeAs($directory, $uniqueName, 'private');

            if (! $path) {
                \Log::error('Bank Transfer file storage failed', [
                    'order_id' => $orderId,
                    'directory' => $directory,
                    'filename' => $uniqueName,
                    'ip_address' => request()->ip(),
                ]);
                
                return false;
            }

            // Log successful upload with security details
            \Log::info('Bank Transfer file stored successfully', [
                'order_id' => $orderId,
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => self::getMimeType($path),
                'ip_address' => request()->ip(),
                'user_id' => auth()->id(),
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Bank Transfer file upload failed', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get secure URL for file access.
     *
     * @param  string  $path
     * @return string
     */
    public static function getSecureUrl(string $path): string
    {
        // This will be handled by controller with authentication
        return route('admin.sales.bank-transfers.file', ['path' => base64_encode($path)]);
    }

    /**
     * Delete file from storage.
     *
     * @param  string  $path
     * @return bool
     */
    public static function delete(string $path): bool
    {
        try {
            if (self::exists($path)) {
                return Storage::disk('private')->delete($path);
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Bank Transfer file deletion failed', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);

            return false;
        }
    }

    /**
     * Check if file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        try {
            return Storage::disk('private')->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file size in human-readable format.
     *
     * @param  string  $path
     * @return string
     */
    public static function getFileSize(string $path): string
    {
        try {
            if (! self::exists($path)) {
                return '0 B';
            }

            $bytes = Storage::disk('private')->size($path);

            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;

            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }

            return round($bytes, 2).' '.$units[$i];
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    /**
     * Get file MIME type.
     *
     * @param  string  $path
     * @return string|null
     */
    public static function getMimeType(string $path): ?string
    {
        try {
            if (! self::exists($path)) {
                return null;
            }

            return Storage::disk('private')->mimeType($path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if file is an image.
     *
     * @param  string  $path
     * @return bool
     */
    public static function isImage(string $path): bool
    {
        $mimeType = self::getMimeType($path);

        return $mimeType && str_starts_with($mimeType, 'image/');
    }

    /**
     * Check if file is a PDF.
     *
     * @param  string  $path
     * @return bool
     */
    public static function isPdf(string $path): bool
    {
        $mimeType = self::getMimeType($path);

        return $mimeType === 'application/pdf';
    }

    /**
     * Perform comprehensive file integrity validation.
     *
     * This method performs additional security checks beyond basic validation:
     * - File corruption detection
     * - Metadata analysis
     * - Size consistency checks
     * - Timestamp validation
     *
     * @param  string  $filePath
     * @return array ['valid' => bool, 'error' => string|null, 'warnings' => array]
     */
    public static function validateFileIntegrity(string $filePath): array
    {
        $warnings = [];

        try {
            if (!file_exists($filePath)) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.file-not-found'),
                    'warnings' => $warnings,
                ];
            }

            // Check file size consistency
            $fileSize = filesize($filePath);
            if ($fileSize === false || $fileSize === 0) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.empty-file'),
                    'warnings' => $warnings,
                ];
            }

            // Check file permissions
            if (!is_readable($filePath)) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.file-not-readable'),
                    'warnings' => $warnings,
                ];
            }

            // Validate file modification time (should be recent)
            $modTime = filemtime($filePath);
            $currentTime = time();
            $timeDiff = $currentTime - $modTime;

            // Warn if file is older than 1 hour (potential replay attack)
            if ($timeDiff > 3600) {
                $warnings[] = 'File modification time is older than expected';
                \Log::warning('Bank Transfer file integrity check - old file detected', [
                    'file_path' => basename($filePath),
                    'mod_time' => date('Y-m-d H:i:s', $modTime),
                    'age_hours' => round($timeDiff / 3600, 2),
                ]);
            }

            // Check for file corruption by reading entire file
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.file-corrupted'),
                    'warnings' => $warnings,
                ];
            }

            // Read file in chunks to detect corruption
            $chunkSize = 8192;
            $totalRead = 0;
            
            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    fclose($handle);
                    return [
                        'valid' => false,
                        'error' => trans('banktransfer::app.shop.errors.file-corrupted'),
                        'warnings' => $warnings,
                    ];
                }
                $totalRead += strlen($chunk);
            }
            
            fclose($handle);

            // Verify total bytes read matches file size
            if ($totalRead !== $fileSize) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.file-size-mismatch'),
                    'warnings' => $warnings,
                ];
            }

            // Additional entropy check for suspicious files
            $entropyCheck = self::checkFileEntropy($filePath);
            if (!$entropyCheck['valid']) {
                return [
                    'valid' => false,
                    'error' => $entropyCheck['error'],
                    'warnings' => $warnings,
                ];
            }

            if (!empty($entropyCheck['warnings'])) {
                $warnings = array_merge($warnings, $entropyCheck['warnings']);
            }

            return [
                'valid' => true,
                'error' => null,
                'warnings' => $warnings,
            ];

        } catch (\Exception $e) {
            \Log::error('Bank Transfer file integrity validation exception', [
                'error' => $e->getMessage(),
                'file_path' => basename($filePath),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.integrity-check-failed'),
                'warnings' => $warnings,
            ];
        }
    }

    /**
     * Check file entropy to detect suspicious content.
     *
     * High entropy might indicate encrypted/compressed malicious content.
     * Low entropy might indicate padding attacks.
     *
     * @param  string  $filePath
     * @return array ['valid' => bool, 'error' => string|null, 'warnings' => array]
     */
    protected static function checkFileEntropy(string $filePath): array
    {
        $warnings = [];

        try {
            // Read a sample of the file (first 4KB)
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.invalid-file'),
                    'warnings' => $warnings,
                ];
            }

            $sample = fread($handle, 4096);
            fclose($handle);

            if (empty($sample)) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.empty-file'),
                    'warnings' => $warnings,
                ];
            }

            // Calculate entropy
            $entropy = self::calculateEntropy($sample);

            // Suspicious entropy levels
            if ($entropy > 7.5) {
                // Very high entropy - might be encrypted/compressed malicious content
                $warnings[] = 'File has unusually high entropy (possible encrypted content)';
                \Log::warning('Bank Transfer high entropy detected', [
                    'file_path' => basename($filePath),
                    'entropy' => $entropy,
                ]);
            } elseif ($entropy < 1.0) {
                // Very low entropy - might be padding attack
                $warnings[] = 'File has unusually low entropy (possible padding attack)';
                \Log::warning('Bank Transfer low entropy detected', [
                    'file_path' => basename($filePath),
                    'entropy' => $entropy,
                ]);
            }

            // Check for excessive null bytes (potential padding attack)
            $nullCount = substr_count($sample, "\x00");
            $nullPercentage = ($nullCount / strlen($sample)) * 100;

            if ($nullPercentage > 50) {
                return [
                    'valid' => false,
                    'error' => trans('banktransfer::app.shop.errors.excessive-null-bytes'),
                    'warnings' => $warnings,
                ];
            } elseif ($nullPercentage > 20) {
                $warnings[] = 'File contains high percentage of null bytes';
            }

            return [
                'valid' => true,
                'error' => null,
                'warnings' => $warnings,
            ];

        } catch (\Exception $e) {
            \Log::error('Bank Transfer entropy check exception', [
                'error' => $e->getMessage(),
                'file_path' => basename($filePath),
            ]);

            return [
                'valid' => false,
                'error' => trans('banktransfer::app.shop.errors.entropy-check-failed'),
                'warnings' => $warnings,
            ];
        }
    }

    /**
     * Calculate Shannon entropy of data.
     *
     * @param  string  $data
     * @return float
     */
    protected static function calculateEntropy(string $data): float
    {
        $length = strlen($data);
        if ($length === 0) {
            return 0;
        }

        // Count frequency of each byte
        $frequencies = array_count_values(str_split($data));
        
        $entropy = 0;
        foreach ($frequencies as $count) {
            $probability = $count / $length;
            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }
}
