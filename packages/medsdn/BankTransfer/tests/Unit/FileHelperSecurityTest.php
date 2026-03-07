<?php

namespace Webkul\BankTransfer\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Webkul\BankTransfer\Helpers\FileHelper;

class FileHelperSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure private disk is available for testing
        Storage::fake('private');
    }

    /** @test */
    public function it_validates_mime_type_using_finfo_file()
    {
        // Create a fake PHP file disguised as JPG
        $file = UploadedFile::fake()->create('malicious.jpg', 100, 'text/x-php');

        $result = FileHelper::validate($file);

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    /** @test */
    public function it_enforces_extension_whitelist()
    {
        $invalidExtensions = ['exe', 'php', 'sh', 'bat', 'js'];

        foreach ($invalidExtensions as $ext) {
            $file = UploadedFile::fake()->create("file.{$ext}", 100);

            $result = FileHelper::validate($file);

            $this->assertFalse($result['valid'], "Extension {$ext} should be rejected");
        }
    }

    /** @test */
    public function it_accepts_valid_extensions()
    {
        $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($validExtensions as $ext) {
            $mimeType = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            };

            $file = UploadedFile::fake()->image("file.{$ext}")->mimeType($mimeType);

            $result = FileHelper::validate($file);

            $this->assertTrue($result['valid'], "Extension {$ext} should be accepted");
        }
        
        // Test PDF separately with a real PDF file
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/Resources <<\n/Font <<\n/F1 4 0 R\n>>\n>>\n/MediaBox [0 0 612 792]\n/Contents 5 0 R\n>>\nendobj\n4 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n5 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Test PDF) Tj\nET\nendstream\nendobj\nxref\n0 6\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000262 00000 n\n0000000341 00000 n\ntrailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n437\n%%EOF";
        
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        fwrite($tempFile, $pdfContent);
        
        $pdfFile = new UploadedFile($tempPath, 'file.pdf', 'application/pdf', null, true);
        $result = FileHelper::validate($pdfFile);
        
        $this->assertTrue($result['valid'], "Extension pdf should be accepted");
        
        fclose($tempFile);
    }

    /** @test */
    public function it_enforces_4mb_size_limit()
    {
        // Create a file larger than 4MB (4097 KB = 4.001 MB)
        $file = UploadedFile::fake()->create('large.jpg', 4097);

        $result = FileHelper::validate($file);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('4MB', $result['error']);
    }

    /** @test */
    public function it_accepts_files_under_4mb()
    {
        // Create a file under 4MB (4000 KB)
        $file = UploadedFile::fake()->image('valid.jpg')->size(4000);

        $result = FileHelper::validate($file);

        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function it_sanitizes_filename_removing_path_traversal()
    {
        $dangerousFilenames = [
            '../../../etc/passwd' => 'etcpasswd',
            '..\\..\\windows\\system32' => 'windowssystem32',
            'file/../../../secret.txt' => 'file_secret.txt',
            '../../file.jpg' => 'file.jpg',
        ];

        foreach ($dangerousFilenames as $dangerous => $expected) {
            $sanitized = FileHelper::sanitizeFilename($dangerous);

            // Assert the sanitized result matches expected value
            $this->assertEquals($expected, $sanitized);
            
            // Additional safety checks
            $this->assertStringNotContainsString('..', $sanitized);
            $this->assertStringNotContainsString('/', $sanitized);
            $this->assertStringNotContainsString('\\', $sanitized);
        }
    }

    /** @test */
    public function it_sanitizes_filename_removing_null_bytes()
    {
        $filename = "file\0.jpg";

        $sanitized = FileHelper::sanitizeFilename($filename);

        $this->assertStringNotContainsString("\0", $sanitized);
    }

    /** @test */
    public function it_sanitizes_filename_removing_special_characters()
    {
        $filename = 'file<>:"|?*.jpg';

        $sanitized = FileHelper::sanitizeFilename($filename);

        // Should only contain alphanumeric, dots, dashes, underscores
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9._-]+$/', $sanitized);
    }

    /** @test */
    public function it_sanitizes_filename_removing_control_characters()
    {
        // ASCII control characters (0-31 and 127)
        $filename = "file\x00\x01\x1F\x7F.jpg";

        $sanitized = FileHelper::sanitizeFilename($filename);

        // Should not contain any control characters
        $this->assertDoesNotMatchRegularExpression('/[\x00-\x1F\x7F]/', $sanitized);
    }

    /** @test */
    public function it_prevents_double_extension_attacks()
    {
        $file = UploadedFile::fake()->create('malicious.php.jpg', 100);

        $result = FileHelper::validate($file);

        // Should detect dangerous extension in multi-part filename
        $this->assertFalse($result['valid']);
    }

    /** @test */
    public function it_detects_mime_extension_mismatch()
    {
        // Create a file with mismatched MIME type and extension
        // This simulates a renamed file (e.g., .exe renamed to .jpg)
        $file = UploadedFile::fake()->create('file.jpg', 100);
        
        // We need to mock finfo_file to return a different MIME type
        // Since we can't easily mock finfo_file, we'll test with a real mismatch
        // by creating a text file with image extension
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        file_put_contents($tempPath, 'This is not an image');
        
        $file = new UploadedFile($tempPath, 'file.jpg', 'text/plain', null, true);

        $result = FileHelper::validate($file);

        $this->assertFalse($result['valid'], 'MIME type mismatch should be detected');
        
        fclose($tempFile);
    }

    /** @test */
    public function it_stores_files_outside_public_web_root()
    {
        $file = UploadedFile::fake()->image('payment.jpg');
        $orderId = 12345;

        $path = FileHelper::store($file, $orderId);

        $this->assertNotFalse($path);
        $this->assertStringStartsWith('bank-transfers/', $path);
        $this->assertStringContainsString((string) $orderId, $path);

        // Verify file is stored in private disk (storage/app/private)
        Storage::disk('private')->assertExists($path);
    }

    /** @test */
    public function it_generates_unique_filenames_to_prevent_collisions()
    {
        $file1 = UploadedFile::fake()->image('payment.jpg');
        $file2 = UploadedFile::fake()->image('payment.jpg');
        $orderId = 12345;

        $path1 = FileHelper::store($file1, $orderId);
        sleep(1); // Ensure different timestamp
        $path2 = FileHelper::store($file2, $orderId);

        $this->assertNotEquals($path1, $path2, 'Filenames should be unique');
    }

    /** @test */
    public function it_limits_filename_length()
    {
        $longFilename = str_repeat('a', 200) . '.jpg';

        $sanitized = FileHelper::sanitizeFilename($longFilename);

        $this->assertLessThanOrEqual(100, strlen($sanitized));
    }

    /** @test */
    public function it_removes_leading_dots_from_filename()
    {
        $filename = '.hidden.jpg';

        $sanitized = FileHelper::sanitizeFilename($filename);

        $this->assertStringStartsNotWith('.', $sanitized);
    }

    /** @test */
    public function it_handles_empty_filename_after_sanitization()
    {
        $filename = '../../../';

        $sanitized = FileHelper::sanitizeFilename($filename);

        $this->assertNotEmpty($sanitized);
        $this->assertEquals('payment_proof', $sanitized);
    }

    /** @test */
    public function it_validates_file_before_storage()
    {
        // Create an invalid file (too large)
        $file = UploadedFile::fake()->create('large.jpg', 5000);
        $orderId = 12345;

        $result = FileHelper::store($file, $orderId);

        $this->assertFalse($result, 'Invalid files should not be stored');
    }

    /** @test */
    public function it_logs_security_violations()
    {
        // Mock non-security logs
        Log::shouldReceive('info')->zeroOrMoreTimes();
        
        // Require at least one security log (warning or error) when rejecting invalid file
        Log::shouldReceive('warning')->atLeast()->once();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        // Attempt to upload invalid file (wrong extension)
        $file = UploadedFile::fake()->create('malicious.exe', 100);
        $orderId = 12345;

        $result = FileHelper::store($file, $orderId);

        $this->assertFalse($result, 'Invalid file should not be stored');
    }
}
