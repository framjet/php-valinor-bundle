<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\DependencyInjection;

use FramJet\Packages\ValinorBundle\ValinorBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const FIELD_CACHE_DIR = 'cache_dir';
    public const FIELD_MAPPERS   = 'mappers';
    public const FIELD_CLASSES   = 'classes';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(ValinorBundle::NAME);

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode(self::FIELD_CACHE_DIR)
                    ->defaultValue('%kernel.cache_dir%/' . ValinorBundle::NAME)
                ->end()
                ->arrayNode(self::FIELD_MAPPERS)
                    ->addDefaultChildrenIfNoneSet(['name' => 'default'])
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode(self::FIELD_CLASSES)
                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->arrayNode(ValinorBundle::PROVIDER_ALTER)
                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->arrayNode(ValinorBundle::PROVIDER_BIND)
                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->arrayNode(ValinorBundle::PROVIDER_INFER)
                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->arrayNode(ValinorBundle::PROVIDER_VISIT)
                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
