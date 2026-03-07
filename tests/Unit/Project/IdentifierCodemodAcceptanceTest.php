<?php

use Symfony\Component\Process\Process;
use Tests\TestCase;

uses(TestCase::class);

function identifierCodemodAdminLayoutFiles(): array
{
    $layoutRoot = dirname(__DIR__, 3).'/packages/medsdn/Admin/src/Resources/views/components/layouts';
    $layoutFiles = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($layoutRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $layoutFile) {
        if (! $layoutFile->isFile() || $layoutFile->getExtension() !== 'php') {
            continue;
        }

        if (! str_ends_with($layoutFile->getFilename(), '.blade.php')) {
            continue;
        }

        $layoutFiles[] = $layoutFile->getPathname();
    }

    sort($layoutFiles);

    return $layoutFiles;
}

function identifierCodemodShouldRegisterLayoutAcceptanceTests(?array $argv = null): bool
{
    $argv ??= $_SERVER['argv'] ?? [];

    if (identifierCodemodHasFilter($argv, 'helper') || identifierCodemodHasFilter($argv, 'dry')) {
        return false;
    }

    return true;
}

function identifierCodemodShouldRegisterDryRunAcceptanceTests(?array $argv = null): bool
{
    $argv ??= $_SERVER['argv'] ?? [];

    return ! identifierCodemodHasFilter($argv, 'helper');
}

function identifierCodemodHasFilter(array $argv, string $needle): bool
{
    $filter = null;

    foreach ($argv as $index => $argument) {
        if (str_starts_with($argument, '--filter=')) {
            $filter = substr($argument, strlen('--filter='));

            break;
        }

        if ($argument === '--filter') {
            $filter = $argv[$index + 1] ?? null;

            break;
        }
    }

    return $filter !== null && str_contains($filter, $needle);
}

function identifierCodemodWriteFixturePaths(string $rootPath): array
{
    return [
        'root_composer' => $rootPath.'/composer.json',
        'admin_composer' => $rootPath.'/packages/medsdn/Admin/composer.json',
        'php' => $rootPath.'/packages/medsdn/Admin/src/Helpers/Reporting.php',
        'config' => $rootPath.'/packages/medsdn/Admin/src/Config/system.php',
        'blade' => $rootPath.'/packages/medsdn/Admin/src/Resources/views/components/layouts/index.blade.php',
        'untouched' => $rootPath.'/packages/medsdn/Admin/src/Resources/assets/images/settings/product.svg',
        'script' => $rootPath.'/scripts/rebrand-identifiers.php',
    ];
}

/**
 * @return array{root: string, paths: array<string, string>}
 */
function identifierCodemodCreateWriteFixture(): array
{
    $sourceRoot = dirname(__DIR__, 3);
    $fixtureRoot = sys_get_temp_dir().'/identifier-codemod-'.bin2hex(random_bytes(10));
    $sourcePaths = identifierCodemodWriteFixturePaths($sourceRoot);
    $fixturePaths = [];

    foreach ($sourcePaths as $key => $sourcePath) {
        expect($sourcePath)->toBeFile();

        $targetPath = str_replace($sourceRoot, $fixtureRoot, $sourcePath);
        $contents = file_get_contents($sourcePath);

        expect($contents)->not->toBeFalse();

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0777, true);
        }

        file_put_contents($targetPath, $contents);

        $fixturePaths[$key] = $targetPath;
    }

    return [
        'root' => $fixtureRoot,
        'paths' => $fixturePaths,
    ];
}

function identifierCodemodRemoveDirectory(string $directory): void
{
    if (! is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());

            continue;
        }

        unlink($item->getPathname());
    }

    rmdir($directory);
}

it('defines the medsdn asset helper compatibility alias', function () {
    expect(identifierCodemodShouldRegisterLayoutAcceptanceTests(['artisan', 'test', '--filter=helper']))->toBeFalse();
    expect(identifierCodemodShouldRegisterLayoutAcceptanceTests(['artisan', 'test', '--filter', 'helper']))->toBeFalse();
    expect(function_exists('medsdn_asset'))->toBeTrue();
    expect(medsdn_asset('images/logo.svg'))->toBe(bagisto_asset('images/logo.svg'));
});

if (identifierCodemodShouldRegisterDryRunAcceptanceTests()) {
    it('reports dry run identifier replacements for the admin scope', function () {
        expect(identifierCodemodShouldRegisterLayoutAcceptanceTests(['artisan', 'test', '--filter=dry']))->toBeFalse();
        expect(identifierCodemodShouldRegisterLayoutAcceptanceTests(['artisan', 'test', '--filter', 'dry']))->toBeFalse();

        $process = new Process([
            PHP_BINARY,
            dirname(__DIR__, 3).'/scripts/rebrand-identifiers.php',
            '--scope=admin',
            '--dry-run',
        ]);

        $process->run();

        $output = $process->getOutput();

        expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
        expect($output)->toContain('DRY RUN: no files were modified.');
        expect($output)->toContain('Scope: admin');
        expect($output)->toContain('Scanned files:');
        expect($output)->toContain('Files with replacements:');
        expect($output)->toContain('Files with pending replacements:');
        expect($output)->toContain('composer.json');
        expect($output)->toContain('packages/medsdn/Admin/composer.json');
        expect($output)->toContain('packages/medsdn/Admin/src/Helpers/Reporting.php');
        expect($output)->toContain('packages/medsdn/Admin/src/Resources/views/components/layouts/index.blade.php');
        expect($output)->toContain('Replacement counts:');
        expect($output)->toContain(' - bagisto_asset:');
        expect($output)->toContain(' - Webkul\\\\Admin\\\\:');
        expect($output)->toMatch('/Scanned files: \d+/');
        expect($output)->toMatch('/Files with replacements: \d+/');
        expect($output)->toMatch('/Files with pending replacements:/');
        expect($output)->toMatch('/ - bagisto_asset: \d+/');
        expect($output)->toMatch('/ - Webkul\\\\Admin\\\\: \d+/');
    });
}

if (identifierCodemodShouldRegisterDryRunAcceptanceTests()) {
    it('writes identifier replacements for the admin scope', function () {
        $fixture = identifierCodemodCreateWriteFixture();
        $paths = $fixture['paths'];
        $untouchedBefore = file_get_contents($paths['untouched']);

        try {
            $process = new Process([
                PHP_BINARY,
                $paths['script'],
                '--scope=admin',
                '--write',
            ]);

            $process->run();

            $output = $process->getOutput();

            expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
            expect($output)->toContain('WRITE MODE: files were modified.');
            expect($output)->toContain('Changed files:');
            expect($output)->toContain('packages/medsdn/Admin/src/Helpers/Reporting.php');
            expect($output)->toContain('packages/medsdn/Admin/src/Resources/views/components/layouts/index.blade.php');
            expect($output)->toContain('composer.json');
            expect($output)->toContain('packages/medsdn/Admin/composer.json');
            expect($output)->not->toContain('packages/medsdn/Admin/src/Config/system.php');

            $rootComposer = file_get_contents($paths['root_composer']);
            $adminComposer = file_get_contents($paths['admin_composer']);
            $phpContents = file_get_contents($paths['php']);
            $configContents = file_get_contents($paths['config']);
            $bladeContents = file_get_contents($paths['blade']);
            $untouchedContents = file_get_contents($paths['untouched']);

            expect($rootComposer)->toContain('"Medsdn\\\\Admin\\\\": "packages/medsdn/Admin/src"');
            expect($rootComposer)->toContain('"Medsdn\\\\Admin\\\\Tests\\\\": "packages/medsdn/Admin/tests"');
            expect($rootComposer)->not->toContain('"Webkul\\\\Admin\\\\": "packages/medsdn/Admin/src"');
            expect($adminComposer)->toContain('"Medsdn\\\\Admin\\\\": "src/"');
            expect($adminComposer)->toContain('"Medsdn\\\\Admin\\\\Providers\\\\AdminServiceProvider"');
            expect($adminComposer)->not->toContain('"Webkul\\\\Admin\\\\": "src/"');
            expect($phpContents)->toContain('namespace Medsdn\Admin\Helpers;');
            expect($phpContents)->toContain('use Medsdn\Admin\Helpers\Reporting\Cart;');
            expect($phpContents)->toContain('use Webkul\Product\Models\Product as ProductModel;');
            expect($phpContents)->not->toContain('namespace Webkul\Admin\Helpers;');
            expect($configContents)->toContain("'options' => 'Webkul\\Tax\\Repositories\\TaxCategoryRepository@getConfigOptions'");
            expect($configContents)->not->toContain("'options' => 'Medsdn\\Tax\\Repositories\\TaxCategoryRepository@getConfigOptions'");
            expect($bladeContents)->toContain('medsdn_asset(');
            expect($bladeContents)->not->toContain('bagisto_asset(');
            expect($untouchedContents)->toBe($untouchedBefore);
        } finally {
            identifierCodemodRemoveDirectory($fixture['root']);
        }
    });
}

if (identifierCodemodShouldRegisterLayoutAcceptanceTests()) {
    foreach (identifierCodemodAdminLayoutFiles() as $bladeFile) {
        $relativeBladePath = str_replace(dirname(__DIR__, 3).'/', '', $bladeFile);

        it('expects '.$relativeBladePath.' to prefer the medsdn asset call after identifier codemod', function () use ($bladeFile, $relativeBladePath) {
            expect($bladeFile)->toBeFile();

            $contents = file_get_contents($bladeFile);
            $usesAssetHelper = str_contains($contents, 'bagisto_asset(') || str_contains($contents, 'medsdn_asset(');

            expect(
                $usesAssetHelper ? str_contains($contents, 'medsdn_asset(') : true,
                $relativeBladePath.' should contain medsdn_asset(',
            )->toBeTrue();

            expect(
                $usesAssetHelper ? str_contains($contents, 'bagisto_asset(') : false,
                $relativeBladePath.' should not contain bagisto_asset(',
            )->toBeFalse();
        });
    }
}
