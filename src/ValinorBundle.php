<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle;

use FramJet\Packages\ValinorBundle\DependencyInjection\CompilerPass\ClassesCompilerPass;
use FramJet\Packages\ValinorBundle\DependencyInjection\CompilerPass\ProvidersInjectionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ValinorBundle extends Bundle
{
    public const NAME                        = 'valinor';
    public const ID_PREFIX                   = self::NAME . '.';
    public const ID_MAPPER_PREFIX            = self::ID_PREFIX . 'mapper.';
    public const ID_MAPPER_BUILDER_PREFIX    = self::ID_PREFIX . 'mapper_builder.';
    public const ID_PROVIDER_PREFIX          = self::ID_PREFIX . 'provider.';
    public const ID_CLASSES_TO_MAPPER_PREFIX = self::ID_PREFIX . 'classes_to_mapper.';

    public const ID_MAPPER_REGISTRY = self::ID_PREFIX . 'mapper_registry';
    public const ID_MAPPER          = self::ID_PREFIX . 'mapper';
    public const ID_CACHE_DIR       = self::ID_PREFIX . 'cache_dir';

    public const MAPPER_TAG         = self::ID_PREFIX . 'mapper';
    public const MAPPER_BUILDER_TAG = self::MAPPER_TAG . '_builder';
    public const CLASS_TAG          = self::NAME . '_class';

    public const MAPPER_TAG_NAME_ATTRIBUTE = 'mapper';

    public const PROVIDER_ALTER = 'alter';
    public const PROVIDER_BIND  = 'bind';
    public const PROVIDER_INFER = 'infer';
    public const PROVIDER_VISIT = 'visit';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ProvidersInjectionCompilerPass());
        $container->addCompilerPass(new ClassesCompilerPass());
    }
}
