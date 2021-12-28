<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\DependencyInjection\CompilerPass;

use FramJet\Packages\ValinorBundle\ValinorBundle;
use FramJet\Packages\ValinorBundle\ValinorCacheWarmer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;
use function is_array;

class ClassesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $classesToMappers = [];

        foreach ($container->findTaggedServiceIds(ValinorBundle::CLASS_TAG) as $serviceId => $tags) {
            $definition      = $container->getDefinition($serviceId);
            $definitionClass = $definition->getClass();

            foreach ($tags as $tag) {
                $classesToMappers[$definitionClass] = $tag[ValinorBundle::MAPPER_TAG_NAME_ATTRIBUTE] ?? 'default';
            }
        }

        foreach ($container->findTaggedServiceIds(ValinorBundle::MAPPER_TAG) as $tags) {
            foreach ($tags as $tag) {
                $mapperName    = $tag[ValinorBundle::MAPPER_TAG_NAME_ATTRIBUTE];
                $mapperClasses = $container->getParameter(ValinorBundle::ID_CLASSES_TO_MAPPER_PREFIX . $mapperName);

                if (is_array($mapperClasses) === false) {
                    $mapperClasses = [];
                }

                foreach ($mapperClasses as $mapperClass) {
                    $classesToMappers[$mapperClass] = $mapperName;
                }
            }
        }

        $container->getDefinition(ValinorCacheWarmer::class)
                  ->setArgument('$classesToWarmUp', array_keys($classesToMappers));

        $container->getDefinition(ValinorBundle::ID_MAPPER_REGISTRY)
                  ->setArgument('$classToMapper', $classesToMappers);
    }
}
