<?php

namespace Webkul\Installer\Support\PackageBootstrap;

class PackageBootstrapStep
{
    public const STRATEGY_SEEDED_BY_INSTALLER = 'seeded_by_installer';
    public const STRATEGY_COMMAND_BOOTSTRAPPED = 'command_bootstrapped';
    public const STRATEGY_MIGRATION_ONLY = 'migration_only';

    /**
     * @param  array<int, array{name: string, parameters?: array<string, mixed>}>  $commands
     */
    public function __construct(
        public readonly string $package,
        public readonly string $strategy,
        public readonly string $description,
        public readonly array $commands = [],
    ) {}

    public static function seeded(string $package, string $description): self
    {
        return new self($package, self::STRATEGY_SEEDED_BY_INSTALLER, $description);
    }

    /**
     * @param  array<int, array{name: string, parameters?: array<string, mixed>}>  $commands
     */
    public static function command(string $package, string $description, array $commands): self
    {
        return new self($package, self::STRATEGY_COMMAND_BOOTSTRAPPED, $description, $commands);
    }

    public static function migrationOnly(string $package, string $description): self
    {
        return new self($package, self::STRATEGY_MIGRATION_ONLY, $description);
    }

    /**
     * @return array<int, string>
     */
    public function commandNames(): array
    {
        return array_values(array_map(
            static fn (array $command): string => $command['name'],
            $this->commands
        ));
    }
}
