<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\DependencyInjection;

use CuyZ\Valinor\Mapper\TreeMapper;
use Exception;
use FramJet\Packages\ValinorBundle\Attributes\ValinorMapped;
use FramJet\Packages\ValinorBundle\Providers\AlterProviderInterface;
use FramJet\Packages\ValinorBundle\Providers\BindProviderInterface;
use FramJet\Packages\ValinorBundle\Providers\InferProviderInterface;
use FramJet\Packages\ValinorBundle\Providers\VisitProviderInterface;
use FramJet\Packages\ValinorBundle\ValinorBundle;
use FramJet\Packages\ValinorBundle\ValinorMapperBuilder;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

use function dirname;
use function sprintf;

class ValinorExtension extends ConfigurableExtension
{
    /**
     * @psalm-param array{ cache_dir: string, mappers: array} $mergedConfig
     *
     * @throws Exception
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.php');

        $this->registerInterfacesAndAttributes($container);

        $cacheDir = $mergedConfig[Configuration::FIELD_CACHE_DIR];
        $container->setParameter(ValinorBundle::ID_CACHE_DIR, $cacheDir);

        foreach ($mergedConfig[Configuration::FIELD_MAPPERS] ?? [] as $name => $mapper) {
            $this->registerMapper($name, $mapper, $cacheDir, $container);
        }
    }

    private function registerInterfacesAndAttributes(ContainerBuilder $container): void
    {
        $this->registerProvider(ValinorBundle::PROVIDER_ALTER, AlterProviderInterface::class, $container);
        $this->registerProvider(ValinorBundle::PROVIDER_BIND, BindProviderInterface::class, $container);
        $this->registerProvider(ValinorBundle::PROVIDER_INFER, InferProviderInterface::class, $container);
        $this->registerProvider(ValinorBundle::PROVIDER_VISIT, VisitProviderInterface::class, $container);

        $container->registerAttributeForAutoconfiguration(ValinorMapped::class, static function (
            ChildDefinition $definition,
            ValinorMapped $attribute,
        ) use ($container): void {
            $mapperName = $attribute->mapperName;
            if ($container->hasDefinition(ValinorBundle::ID_MAPPER_PREFIX . $mapperName) === false) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Valinor Mapper with name "%s" is not defined',
                        $mapperName
                    )
                );
            }

            $definition->addTag(ValinorBundle::CLASS_TAG, [ValinorBundle::MAPPER_TAG_NAME_ATTRIBUTE => $mapperName]);
        });
    }

    private function registerMapper(string $name, array $config, string $cacheDir, ContainerBuilder $container): void
    {
        $builderId = ValinorBundle::ID_MAPPER_BUILDER_PREFIX . $name;
        $mapperId  = ValinorBundle::ID_MAPPER_PREFIX . $name;

        $builderDef = new Definition(ValinorMapperBuilder::class, [$cacheDir]);
        $builderDef->addTag(ValinorBundle::MAPPER_BUILDER_TAG, [ValinorBundle::MAPPER_TAG_NAME_ATTRIBUTE => $name]);

        $mapperDef = new Definition(TreeMapper::class);
        $mapperDef->setFactory([new Reference($builderId), 'build']);
        $mapperDef->addTag(ValinorBundle::MAPPER_TAG, [ValinorBundle::MAPPER_TAG_NAME_ATTRIBUTE => $name]);
        $mapperDef->setPublic(true);

        $container->setParameter(
            ValinorBundle::ID_CLASSES_TO_MAPPER_PREFIX . $name,
            $config[Configuration::FIELD_CLASSES]
        );

        $this->registerMapperBuilderProvider(ValinorBundle::PROVIDER_ALTER, $builderDef, $config);
        $this->registerMapperBuilderProvider(ValinorBundle::PROVIDER_BIND, $builderDef, $config);
        $this->registerMapperBuilderProvider(ValinorBundle::PROVIDER_INFER, $builderDef, $config);
        $this->registerMapperBuilderProvider(ValinorBundle::PROVIDER_VISIT, $builderDef, $config);

        $container->setDefinition($builderId, $builderDef);
        $container->setDefinition($mapperId, $mapperDef);
    }

    private function registerMapperBuilderProvider(string $type, Definition $definition, array $config): void
    {
        $methodName = match ($type) {
            ValinorBundle::PROVIDER_ALTER => 'addAlterProvider',
            ValinorBundle::PROVIDER_BIND => 'addBindProvider',
            ValinorBundle::PROVIDER_INFER => 'addInferProvider',
            ValinorBundle::PROVIDER_VISIT => 'addVisitProvider',
        };

        foreach ($config[$type] ?? [] as $provider) {
            $definition->addMethodCall($methodName, [new Reference($provider)]);
        }
    }

    private function registerProvider(string $type, string $interface, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration($interface)
                  ->addTag(ValinorBundle::ID_PROVIDER_PREFIX . $type);
    }
}
