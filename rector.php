<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets(php81: true)
    // ->withTypeCoverageLevel(1)
    // ->withPreparedSets(typeDeclarations: true)
    // ->withDeadCodeLevel(1)
    // ->withImportNames(removeUnusedImports: true)
    ->withRules([
        OptionalParametersAfterRequiredRector::class,
        RemoveUnusedVariableInCatchRector::class,
        RemoveExtraParametersRector::class,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyPropertyRector::class,
    ]);
