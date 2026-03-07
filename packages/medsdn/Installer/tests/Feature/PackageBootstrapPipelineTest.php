<?php

use Illuminate\Support\Facades\File;
use Webkul\Installer\Support\PackageBootstrap\PackageBootstrapRegistry;
use Webkul\Installer\Support\PackageBootstrap\PackageBootstrapRunner;
use Webkul\Installer\Support\PackageBootstrap\PackageBootstrapStep;

it('accounts for every first-party package in the installer bootstrap registry', function () {
    $packageNames = collect(File::directories(base_path('packages/medsdn')))
        ->map(fn (string $path) => basename($path))
        ->sort()
        ->values()
        ->all();

    $registry = new PackageBootstrapRegistry;

    $registeredPackages = array_keys($registry->all());
    sort($registeredPackages);

    expect($registeredPackages)->toBe($packageNames);
});

it('classifies known packages into seeded, command, and migration-only strategies', function () {
    $registry = new PackageBootstrapRegistry;

    $definitions = $registry->all();

    expect($definitions['Attribute']->strategy)->toBe(PackageBootstrapStep::STRATEGY_SEEDED_BY_INSTALLER);
    expect($definitions['Theme']->strategy)->toBe(PackageBootstrapStep::STRATEGY_SEEDED_BY_INSTALLER);
    expect($definitions['GraphQLAPI']->strategy)->toBe(PackageBootstrapStep::STRATEGY_COMMAND_BOOTSTRAPPED);
    expect($definitions['MedsdnApi']->strategy)->toBe(PackageBootstrapStep::STRATEGY_COMMAND_BOOTSTRAPPED);
    expect($definitions['Checkout']->strategy)->toBe(PackageBootstrapStep::STRATEGY_MIGRATION_ONLY);
});

it('registers expected command-driven package bootstrap steps in order', function () {
    $registry = new PackageBootstrapRegistry;

    $steps = $registry->commandBootstrapped();

    expect(array_map(fn (PackageBootstrapStep $step) => $step->package, $steps))
        ->toBe([
            'GraphQLAPI',
            'MedsdnApi',
            'Product',
            'CatalogRule',
        ]);

    expect($steps[0]->commandNames())->toBe(['medsdn-graphql:install']);
    expect($steps[1]->commandNames())->toBe(['medsdn-api-platform:install', 'medsdn-api:generate-key']);
    expect($steps[2]->commandNames())->toBe(['indexer:index']);
    expect($steps[3]->commandNames())->toBe(['product:price-rule:index']);
});

it('executes command bootstrap steps in deterministic order', function () {
    $executed = [];
    $reported = [];

    $runner = new PackageBootstrapRunner(
        commandExecutor: function (string $command, array $parameters) use (&$executed): int {
            $executed[] = [$command, $parameters];

            return 0;
        },
        commandExists: fn (string $command): bool => true,
    );

    $results = $runner->run([
        PackageBootstrapStep::command('GraphQLAPI', 'Install GraphQL API', [
            ['name' => 'medsdn-graphql:install'],
        ]),
        PackageBootstrapStep::command('MedsdnApi', 'Install storefront API', [
            ['name' => 'medsdn-api-platform:install'],
            ['name' => 'medsdn-api:generate-key', 'parameters' => ['--name' => 'Default Storefront Key']],
        ]),
    ], function (string $level, string $message) use (&$reported): void {
        $reported[] = [$level, $message];
    });

    expect($executed)->toBe([
        ['medsdn-graphql:install', []],
        ['medsdn-api-platform:install', []],
        ['medsdn-api:generate-key', ['--name' => 'Default Storefront Key']],
    ]);

    expect(array_column($results, 'status'))->toBe(['completed', 'completed']);
    expect($reported)->not->toBeEmpty();
});

it('skips unavailable bootstrap commands without failing the installer pipeline', function () {
    $runner = new PackageBootstrapRunner(
        commandExecutor: fn (string $command, array $parameters): int => 0,
        commandExists: fn (string $command): bool => $command !== 'medsdn-graphql:install',
    );

    $results = $runner->run([
        PackageBootstrapStep::command('GraphQLAPI', 'Install GraphQL API', [
            ['name' => 'medsdn-graphql:install'],
        ]),
    ]);

    expect($results)->toHaveCount(1);
    expect($results[0]['status'])->toBe('skipped');
    expect($results[0]['details'][0])->toContain('medsdn-graphql:install');
});
