<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle;

use CuyZ\Valinor\Cache\ChainCache;
use CuyZ\Valinor\Cache\Compiled\CompiledPhpFileCache;
use CuyZ\Valinor\Cache\RuntimeCache;
use CuyZ\Valinor\Cache\VersionedCache;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Repository\Cache\CacheClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Definition\Repository\Reflection\CombinedAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Template\BasicTemplateParser;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ValinorCacheWarmer implements CacheWarmerInterface
{
    private CacheClassDefinitionRepository $repository;

    /** @psalm-param list<class-string> $classesToWarmUp */
    public function __construct(
        private string $cacheDir,
        private array $classesToWarmUp = []
    ) {
        $repository = new ReflectionClassDefinitionRepository(
            new LexingTypeParserFactory(
                new BasicTemplateParser()
            ),
            new CombinedAttributesRepository()
        );

        /** @var CacheInterface<ClassDefinition> $cache */
        $cache = new CompiledPhpFileCache($this->cacheDir, new ClassDefinitionCompiler());
        $cache = new VersionedCache(
            new ChainCache(new RuntimeCache(), $cache)
        );

        $this->repository = new CacheClassDefinitionRepository($repository, $cache);
    }

    public function isOptional(): bool
    {
        return true;
    }

    /** @return list<string> */
    public function warmUp(string $cacheDir): array
    {
        foreach ($this->classesToWarmUp as $className) {
            $this->repository->for(new ClassSignature($className));
        }

        return [];
    }
}
