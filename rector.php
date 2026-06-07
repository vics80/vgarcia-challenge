<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
    )
    ->withSkip([
        FlipTypeControlToUseExclusiveTypeRector::class,
    ]);
