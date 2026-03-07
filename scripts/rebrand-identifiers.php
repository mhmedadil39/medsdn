#!/usr/bin/env php
<?php

declare(strict_types=1);

const EXIT_SUCCESS = 0;
const EXIT_INVALID_ARGUMENTS = 1;
const EXIT_WRITE_FAILURE = 2;

main($argv);

function main(array $argv): void
{
    $configuration = parseArguments($argv);
    $rootPath = dirname(__DIR__);

    if ($configuration['mode'] === 'write') {
        $report = writeScopeChanges($configuration['scope'], $rootPath);

        fwrite(STDOUT, formatWriteReport($configuration['scope'], $report));

        exit(EXIT_SUCCESS);
    }

    $report = buildDryRunReport($configuration['scope'], $rootPath);

    fwrite(STDOUT, formatDryRunReport($configuration['scope'], $report));

    exit(EXIT_SUCCESS);
}

/**
 * @return array{scope: string, mode: string}
 */
function parseArguments(array $argv): array
{
    $scope = null;
    $mode = 'dry-run';

    $arguments = array_slice($argv, 1);

    foreach ($arguments as $index => $argument) {
        if (str_starts_with($argument, '--scope=')) {
            $scope = substr($argument, strlen('--scope='));

            continue;
        }

        if ($argument === '--scope') {
            $scope = $arguments[$index + 1] ?? null;

            continue;
        }

        if ($argument === '--dry-run') {
            $mode = 'dry-run';

            continue;
        }

        if ($argument === '--write') {
            $mode = 'write';

            continue;
        }
    }

    if ($scope !== 'admin') {
        fwrite(STDERR, "Unsupported or missing --scope. Supported scopes: admin.\n");

        exit(EXIT_INVALID_ARGUMENTS);
    }

    return [
        'scope' => $scope,
        'mode' => $mode,
    ];
}

/**
 * @return list<string>
 */
function collectScopePaths(string $scope, string $rootPath): array
{
    $paths = match ($scope) {
        'admin' => [$rootPath.'/packages/medsdn/Admin/src'],
        default => [],
    };

    $existingPaths = array_values(array_filter(
        $paths,
        static fn (string $path): bool => is_dir($path)
    ));

    if ($existingPaths === []) {
        fwrite(STDERR, "No directories found for scope [{$scope}].\n");

        exit(EXIT_INVALID_ARGUMENTS);
    }

    return $existingPaths;
}

/**
 * @return array{scanned_files: int, changed_files: int, replacement_counts: array<string, int>, changed_paths: list<string>}
 */
function buildDryRunReport(string $scope, string $rootPath): array
{
    return buildChangeReport(collectWriteTargetFiles($scope, $rootPath), false);
}

/**
 * @return array{scanned_files: int, changed_files: int, replacement_counts: array<string, int>, changed_paths: list<string>}
 */
function writeScopeChanges(string $scope, string $rootPath): array
{
    return buildChangeReport(collectWriteTargetFiles($scope, $rootPath), true);
}

/**
 * @param  array<string, string>  $targetFiles
 * @return array{scanned_files: int, changed_files: int, replacement_counts: array<string, int>, changed_paths: list<string>}
 */
function buildChangeReport(array $targetFiles, bool $writeChanges): array
{
    $report = [
        'scanned_files' => 0,
        'changed_files' => 0,
        'replacement_counts' => replacementCountTemplate(),
        'changed_paths' => [],
    ];

    foreach ($targetFiles as $relativePath => $absolutePath) {
        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            continue;
        }

        $report['scanned_files']++;

        [$updatedContents, $replacementCounts] = applyWriteTransformations($relativePath, $contents);

        foreach ($replacementCounts as $label => $count) {
            $report['replacement_counts'][$label] += $count;
        }

        if ($updatedContents === $contents) {
            continue;
        }

        if ($writeChanges && file_put_contents($absolutePath, $updatedContents) === false) {
            fwrite(STDERR, "Failed to write updated contents to [{$relativePath}].\n");

            exit(EXIT_WRITE_FAILURE);
        }

        $report['changed_files']++;
        $report['changed_paths'][] = $relativePath;
    }

    sort($report['changed_paths']);

    return $report;
}

/**
 * @return array<string, int>
 */
function replacementCountTemplate(): array
{
    return [
        'bagisto_asset' => 0,
        'Webkul\\\\Admin\\\\' => 0,
    ];
}

/**
 * @return array<string, string>
 */
function collectWriteTargetFiles(string $scope, string $rootPath): array
{
    $targetFiles = [
        'composer.json' => $rootPath.'/composer.json',
        'packages/medsdn/Admin/composer.json' => $rootPath.'/packages/medsdn/Admin/composer.json',
    ];

    foreach (collectScopePaths($scope, $rootPath) as $scopePath) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scopePath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || ! shouldInspectFile($file)) {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($rootPath) + 1);

            $targetFiles[$relativePath] = $file->getPathname();
        }
    }

    ksort($targetFiles);

    return $targetFiles;
}

/**
 * @return array{0: string, 1: array<string, int>}
 */
function applyWriteTransformations(string $relativePath, string $contents): array
{
    $updatedContents = $contents;
    $replacementCounts = replacementCountTemplate();

    foreach (writeReplacementRules($relativePath) as $rule) {
        $matches = substr_count($updatedContents, $rule['search']);

        if ($matches === 0) {
            continue;
        }

        $replacementCounts[$rule['label']] += $matches;
        $updatedContents = str_replace($rule['search'], $rule['replacement'], $updatedContents);
    }

    return [$updatedContents, $replacementCounts];
}

/**
 * @return list<array{label: string, search: string, replacement: string}>
 */
function writeReplacementRules(string $relativePath): array
{
    if ($relativePath === 'composer.json') {
        return [
            [
                'label' => 'Webkul\\\\Admin\\\\',
                'search' => 'Webkul\\\\Admin\\\\Tests\\\\',
                'replacement' => 'Medsdn\\\\Admin\\\\Tests\\\\',
            ],
            [
                'label' => 'Webkul\\\\Admin\\\\',
                'search' => 'Webkul\\\\Admin\\\\',
                'replacement' => 'Medsdn\\\\Admin\\\\',
            ],
        ];
    }

    if ($relativePath === 'packages/medsdn/Admin/composer.json') {
        return [
            [
                'label' => 'Webkul\\\\Admin\\\\',
                'search' => 'Webkul\\\\Admin\\\\Providers\\\\AdminServiceProvider',
                'replacement' => 'Medsdn\\\\Admin\\\\Providers\\\\AdminServiceProvider',
            ],
            [
                'label' => 'Webkul\\\\Admin\\\\',
                'search' => 'Webkul\\\\Admin\\\\',
                'replacement' => 'Medsdn\\\\Admin\\\\',
            ],
        ];
    }

    return [
        [
            'label' => 'bagisto_asset',
            'search' => 'bagisto_asset',
            'replacement' => 'medsdn_asset',
        ],
        [
            'label' => 'Webkul\\\\Admin\\\\',
            'search' => 'Webkul\\Admin\\',
            'replacement' => 'Medsdn\\Admin\\',
        ],
    ];
}

function shouldInspectFile(SplFileInfo $file): bool
{
    $path = $file->getPathname();

    return str_ends_with($path, '.php')
        || str_ends_with($path, '.blade.php');
}

/**
 * @param  array{scanned_files: int, changed_files: int, replacement_counts: array<string, int>, changed_paths: list<string>}  $report
 */
function formatDryRunReport(string $scope, array $report): string
{
    $lines = [
        'DRY RUN: no files were modified.',
        "Scope: {$scope}",
        'Scanned files: '.$report['scanned_files'],
        'Files with replacements: '.$report['changed_files'],
        'Files with pending replacements:',
    ];

    if ($report['changed_paths'] === []) {
        $lines[] = ' - none';
    } else {
        foreach ($report['changed_paths'] as $path) {
            $lines[] = " - {$path}";
        }
    }

    $lines[] = 'Replacement counts:';

    foreach ($report['replacement_counts'] as $search => $count) {
        $lines[] = " - {$search}: {$count}";
    }

    return implode(PHP_EOL, $lines).PHP_EOL;
}

/**
 * @param  array{scanned_files: int, changed_files: int, replacement_counts: array<string, int>, changed_paths: list<string>}  $report
 */
function formatWriteReport(string $scope, array $report): string
{
    $lines = [
        'WRITE MODE: files were modified.',
        "Scope: {$scope}",
        'Scanned files: '.$report['scanned_files'],
        'Files changed: '.$report['changed_files'],
        'Changed files:',
    ];

    if ($report['changed_paths'] === []) {
        $lines[] = ' - none';
    } else {
        foreach ($report['changed_paths'] as $path) {
            $lines[] = " - {$path}";
        }
    }

    $lines[] = 'Replacement counts:';

    foreach ($report['replacement_counts'] as $search => $count) {
        $lines[] = " - {$search}: {$count}";
    }

    return implode(PHP_EOL, $lines).PHP_EOL;
}
