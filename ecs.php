<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use craft\ecs\SetList;

return static function(ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::CRAFT_CMS_4);

    $services = $containerConfigurator->services();

    $services->set(OrderedImportsFixer::class)->call('configure', [[
        'sort_algorithm' => OrderedImportsFixer::SORT_NONE,
    ]]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __FILE__,
    ]);
};
