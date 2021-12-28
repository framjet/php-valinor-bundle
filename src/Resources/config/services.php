<?php

declare(strict_types=1);

use CuyZ\Valinor\Mapper\TreeMapper;
use FramJet\Packages\ValinorBundle\ValinorBundle;
use FramJet\Packages\ValinorBundle\ValinorCacheWarmer;
use FramJet\Packages\ValinorBundle\ValinorMapperRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set(ValinorBundle::ID_MAPPER_REGISTRY, ValinorMapperRegistry::class)
             ->public()
             ->arg(
                 0,
                 tagged_iterator(
                     ValinorBundle::MAPPER_TAG,
                     ValinorBundle::MAPPER_TAG_NAME_ATTRIBUTE,
                 ),
             );

    $services->alias(ValinorMapperRegistry::class, ValinorBundle::ID_MAPPER_REGISTRY);
    $services->alias(ValinorBundle::ID_MAPPER, ValinorBundle::ID_MAPPER_REGISTRY);
    $services->alias(TreeMapper::class, ValinorBundle::ID_MAPPER_REGISTRY);

    $services->set(ValinorCacheWarmer::class)
             ->arg('$cacheDir', '%' . ValinorBundle::ID_CACHE_DIR . '%')
             ->arg('$classesToWarmUp', [])
             ->tag('kernel.cache_warmer');
};
