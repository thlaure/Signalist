<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php85\Rector\Property\AddOverrideAttributeToOverriddenPropertiesRector;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withImportNames(importShortClasses: false)
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/migrations',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/var',
        __DIR__.'/vendor',
        __DIR__.'/config/reference.php',
        // Keep #[Override] out of the codebase (the method rule no longer exists).
        AddOverrideAttributeToOverriddenPropertiesRector::class,
    ])
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    )
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        behat: true,
    )
    // Applies the upgrade sets matching the installed Symfony, Doctrine and PHPUnit versions.
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::CONFIGS,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ]);
