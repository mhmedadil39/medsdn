<?php

namespace Webkul\MedsdnApi\Tests\Unit;

use PHPUnit\Framework\TestCase;

class MedsdnApiServiceProviderSourceTest extends TestCase
{
    public function test_module_provider_is_registered_during_register_phase(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/src/Providers/MedsdnApiServiceProvider.php');

        $registerBlock = $this->extractMethod($source, 'register');

        $this->assertStringContainsString('ModuleServiceProvider::class', $registerBlock);
    }

    protected function extractMethod(string $source, string $name): string
    {
        $start = strpos($source, 'function '.$name);

        $this->assertNotFalse($start, sprintf('Method %s not found in source.', $name));

        $bodyStart = strpos($source, '{', $start);
        $this->assertNotFalse($bodyStart, sprintf('Method %s body not found in source.', $name));

        $depth = 0;
        $length = strlen($source);

        for ($index = $bodyStart; $index < $length; $index++) {
            $char = $source[$index];

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($source, $start, $index - $start + 1);
                }
            }
        }

        $this->fail(sprintf('Method %s closing brace not found in source.', $name));
    }
}
