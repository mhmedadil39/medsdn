<?php

it('uses medsdn command prefixes for first-party artisan commands', function () {
    $projectRoot = dirname(__DIR__, 3);

    $versionCommand = file_get_contents($projectRoot.'/packages/medsdn/Core/src/Console/Commands/BagistoVersion.php');
    $translationsCommand = file_get_contents($projectRoot.'/packages/medsdn/Core/src/Console/Commands/TranslationsChecker.php');
    $installerCommand = file_get_contents($projectRoot.'/packages/medsdn/Installer/src/Console/Commands/Installer.php');
    $fakerCommand = file_get_contents($projectRoot.'/app/Console/Commands/MedsdnFake.php');

    expect($versionCommand)->toContain("protected \$signature = 'medsdn:version';")
        ->not->toContain("protected \$signature = 'bagisto:version';");

    expect($translationsCommand)->toContain("protected \$signature = 'medsdn:translations:check")
        ->not->toContain("protected \$signature = 'bagisto:translations:check");

    expect($installerCommand)->toContain("protected \$signature = 'medsdn:install")
        ->toContain("protected \$description = 'MedSDN installer.';")
        ->not->toContain("protected \$signature = 'bagisto:install");

    expect($fakerCommand)->toContain("protected \$signature = 'medsdn:fake';")
        ->not->toContain("protected \$signature = 'bagisto:fake';");
});

it('disables the legacy datafaker package discovery and registers the medsdn faker provider', function () {
    $projectRoot = dirname(__DIR__, 3);

    $composer = file_get_contents($projectRoot.'/composer.json');
    $providers = file_get_contents($projectRoot.'/bootstrap/providers.php');

    expect($composer)->toContain('"bagisto/laravel-datafaker"');
    expect($providers)->toContain('App\\Providers\\MedsdnFakerServiceProvider::class');
});
