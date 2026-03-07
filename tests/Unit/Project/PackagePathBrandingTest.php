<?php

it('uses packages medsdn paths in composer and test bootstrap files', function () {
    $projectRoot = dirname(__DIR__, 3);

    $composer = file_get_contents($projectRoot.'/composer.json');
    $pest = file_get_contents($projectRoot.'/tests/Pest.php');
    $phpunit = file_get_contents($projectRoot.'/phpunit.xml');

    expect($composer)->toContain('packages/medsdn/Admin/src')
        ->toContain('packages/medsdn/Core/src')
        ->not->toContain('packages/Webkul/Admin/src');

    expect($pest)->toContain('../packages/medsdn/Admin/tests')
        ->toContain('../packages/medsdn/Core/tests')
        ->not->toContain('../packages/Webkul/Admin/tests');

    expect($phpunit)->toContain('packages/medsdn/Admin/tests/Feature')
        ->toContain('packages/medsdn/Core/tests/Unit')
        ->not->toContain('packages/Webkul/Admin/tests/Feature');
});
