<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\DependencyInjection\CompilerPass;

use FramJet\Packages\ValinorBundle\ValinorBundle;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function sprintf;

class ProvidersInjectionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->injectProviderType(ValinorBundle::PROVIDER_ALTER, $container);
        $this->injectProviderType(ValinorBundle::PROVIDER_BIND, $container);
        $this->injectProviderType(ValinorBundle::PROVIDER_INFER, $container);
        $this->injectProviderType(ValinorBundle::PROVIDER_VISIT, $container);
    }

    private function injectProviderType(string $type, ContainerBuilder $container): void
    {
        $methodName = match ($type) {
            ValinorBundle::PROVIDER_ALTER => 'addAlterProvider',
            ValinorBundle::PROVIDER_BIND => 'addBindProvider',
            ValinorBundle::PROVIDER_INFER => 'addInferProvider',
            ValinorBundle::PROVIDER_VISIT => 'addVisitProvider',
        };

        foreach ($container->findTaggedServiceIds(ValinorBundle::ID_PROVIDER_PREFIX . $type) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $mapperName = $tag['mapper'] ?? 'default';
                $mapperId   = ValinorBundle::ID_MAPPER_BUILDER_PREFIX . $mapperName;

                if ($container->hasDefinition($mapperId) === false) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Valinor Mapper with name "%s" is not defined',
                            $mapperName
                        )
                    );
                }

                $container->getDefinition($mapperId)
                          ->addMethodCall($methodName, [new Reference($serviceId)]);
            }
        }
    }
}
