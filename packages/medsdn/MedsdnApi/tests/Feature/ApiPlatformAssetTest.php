<?php

namespace Tests\Feature\MedsdnApi;

use Tests\TestCase;

class ApiPlatformAssetTest extends TestCase
{
    public function test_api_platform_swagger_bundle_asset_is_served(): void
    {
        $response = $this->get('/vendor/api-platform/swagger-ui/swagger-ui-bundle.js');

        $response->assertOk();
        $response->assertHeader('Cache-Control', 'public');
        $response->assertHeader('Content-Length');
        $this->assertStringContainsString('javascript', (string) $response->headers->get('Content-Type'));
        $this->assertGreaterThan(1000, (int) $response->headers->get('Content-Length'));
    }

    public function test_api_platform_assets_reject_path_traversal(): void
    {
        $response = $this->get('/vendor/api-platform/%2e%2e/%2e%2e/.env');

        $response->assertNotFound();
    }
}
