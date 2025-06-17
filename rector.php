<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__.'/app',
            __DIR__.'/bootstrap/app.php',
            __DIR__.'/config',
            __DIR__.'/public',
            __DIR__.'/resources',
            __DIR__.'/routes',
            __DIR__.'/tests',
            __DIR__.'/Modules',
        ])

        // Enable caching for Rector
        ->withCache(__DIR__.'/storage/rector')

        // Apply sets for Laravel and general code quality
        ->withSets([
            LaravelLevelSetList::UP_TO_LARAVEL_120,
            LaravelSetList::LARAVEL_CODE_QUALITY,
            LaravelSetList::LARAVEL_COLLECTION,
            // //LaravelSetList::LARAVEL_STATIC_TO_INJECTION,
            // LaravelSetList::LARAVEL_IF_HELPERS,
            // LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
            // LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
            // LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
            // LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
            // LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL
        ])
        ->withRules([])

        // Define PHP version for Rector
        ->withPhpSets(php84: true);

} catch (InvalidConfigurationException  $e) {

}
