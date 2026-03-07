<?php

namespace Webkul\Payment\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaymentServiceProviderSourceTest extends TestCase
{
    public function test_module_and_event_providers_are_registered_during_register_phase(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/src/Providers/PaymentServiceProvider.php');

        $registerBlock = $this->extractMethod($source, 'register');
        $bootBlock = $this->extractMethod($source, 'boot');

        $this->assertStringContainsString('ModuleServiceProvider::class', $registerBlock);
        $this->assertStringContainsString('EventServiceProvider::class', $registerBlock);
        $this->assertStringNotContainsString('ModuleServiceProvider::class', $bootBlock);
        $this->assertStringNotContainsString('EventServiceProvider::class', $bootBlock);
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
