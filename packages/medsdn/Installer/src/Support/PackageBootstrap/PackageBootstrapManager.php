<?php

namespace Webkul\Installer\Support\PackageBootstrap;

use Closure;
use Illuminate\Support\Facades\Artisan;

class PackageBootstrapManager
{
    public function __construct(
        protected PackageBootstrapRegistry $registry,
    ) {}

    /**
     * @param  null|Closure(string, string): void  $report
     * @return array<int, array{package: string, status: string, details: array<int, string>}>
     */
    public function bootstrap(?Closure $report = null): array
    {
        $runner = new PackageBootstrapRunner(
            commandExecutor: fn (string $command, array $parameters): int => Artisan::call($command, $parameters),
            commandExists: fn (string $command): bool => array_key_exists($command, Artisan::all()),
        );

        return $runner->run($this->registry->commandBootstrapped(), $report);
    }
}
