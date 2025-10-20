<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $config): void {
    $config->bootstrapFiles([__DIR__ . '/stubs.php']);

    $config->paths([__DIR__]);

    $config->phpVersion(PhpVersion::PHP_80);

    // Limit scope to the add-on
    $config->paths([
        __DIR__,
    ]);

    // Skip third-party & legacy entry points
    $config->skip([
        __DIR__ . '/vendor',
        NewMethodCallWithoutParenthesesRector::class,
    ]);

    $config->sets([
        JsonThrowOnErrorRector::class,
        SensitiveConstantNameRector::class,
        RemoveExtraParametersRector::class,
        RemoveExtraParametersRector::class,
        BinaryOpBetweenNumberAndStringRector::class,
    ]);

    $config->importNames();
};
