<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\DeadCode\Rector\For_\RemoveDeadLoopRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php83: true)
    ->withSets([
        PHPUnitSetList::PHPUNIT_110,
    ])
    ->withTypeCoverageLevel(45)
    ->withDeadCodeLevel(45)
    // ->withPreparedSets(typeDeclarations: true)
    ->withImportNames(
        removeUnusedImports: true,
        importShortClasses: false
    )
    ->withSkip([
        // Rules
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyPropertyRector::class,
        FirstClassCallableRector::class => [
            __DIR__.'/tests/Unit/Common/Adapter/Event/EventDispatcherSymfonyAdapterTest.php',
        ],
        __DIR__.'/src/Common/Adapter/Jwt/JwtFirebaseHS256Adapter.php',

        // Dead Code
        RemoveDeadIfForeachForRector::class,
        RemoveDeadLoopRector::class,
    ]);
