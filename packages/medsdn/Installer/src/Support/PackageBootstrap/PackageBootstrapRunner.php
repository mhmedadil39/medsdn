<?php

namespace Webkul\Installer\Support\PackageBootstrap;

use Closure;
use RuntimeException;

class PackageBootstrapRunner
{
    public function __construct(
        protected ?Closure $commandExecutor = null,
        protected ?Closure $commandExists = null,
    ) {}

    /**
     * @param  array<int, PackageBootstrapStep>  $steps
     * @param  null|Closure(string, string): void  $report
     * @return array<int, array{package: string, status: string, details: array<int, string>}>
     */
    public function run(array $steps, ?Closure $report = null): array
    {
        $results = [];

        foreach ($steps as $step) {
            $details = [];
            $status = 'completed';

            $report?->__invoke('info', "Bootstrapping {$step->package}: {$step->description}");

            foreach ($step->commands as $command) {
                $name = $command['name'];
                $parameters = $command['parameters'] ?? [];

                if (! $this->commandExists($name)) {
                    $status = 'skipped';
                    $message = "Skipped unavailable command [{$name}] for {$step->package}.";
                    $details[] = $message;
                    $report?->__invoke('warn', $message);

                    continue;
                }

                $exitCode = $this->executeCommand($name, $parameters);

                if ($exitCode !== 0) {
                    throw new RuntimeException("Package bootstrap command [{$name}] for {$step->package} failed with exit code {$exitCode}.");
                }

                $details[] = "Executed [{$name}] for {$step->package}.";
                $report?->__invoke('info', "Executed [{$name}] for {$step->package}.");
            }

            $results[] = [
                'package' => $step->package,
                'status'  => $status,
                'details' => $details,
            ];
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    protected function executeCommand(string $command, array $parameters): int
    {
        if ($this->commandExecutor) {
            return ($this->commandExecutor)($command, $parameters);
        }

        return 0;
    }

    protected function commandExists(string $command): bool
    {
        if ($this->commandExists) {
            return (bool) ($this->commandExists)($command);
        }

        return true;
    }
}
