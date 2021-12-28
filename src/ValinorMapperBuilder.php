<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle;

use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use FramJet\Packages\ValinorBundle\Providers\AlterProviderInterface;
use FramJet\Packages\ValinorBundle\Providers\BindProviderInterface;
use FramJet\Packages\ValinorBundle\Providers\InferProviderInterface;
use FramJet\Packages\ValinorBundle\Providers\VisitProviderInterface;
use InvalidArgumentException;

/**
 * @psalm-type InferClosure = callable(Shell): class-string
 * @psalm-type BindClosure = callable(mixed): object
 * @psalm-type AlterClosure<A> = callable(A): A
 * @psalm-type VisitClosure = callable(Node): void
 */
class ValinorMapperBuilder
{
    /** @var array<class-string, InferProviderInterface|InferClosure> */
    private array $infer = [];

    /** @var list<BindClosure> */
    private array $bind = [];

    /**
     * @template T
     * @var list<AlterClosure<T>>
     */
    private array $alter = [];

    /** @var list<VisitClosure> */
    private array $visit = [];

    public function __construct(private string $cacheDir)
    {
    }

    /**
     * @param InferProviderInterface|InferClosure $provider
     *
     * @return $this
     */
    public function addInferProvider(InferProviderInterface|callable $provider, ?string $interfaceName = null): self
    {
        if ($provider instanceof InferProviderInterface) {
            $interfaceName = $provider->getInterfaceName();
        } elseif ($interfaceName === null) {
            throw new InvalidArgumentException('Interface name must be provided if $provider is callable');
        }

        $this->infer[$interfaceName] = $provider;

        return $this;
    }

    /**
     * @param BindClosure|BindProviderInterface $provider
     *
     * @return $this
     */
    public function addBindProvider(BindProviderInterface|callable $provider): self
    {
        $this->bind[] = $provider;

        return $this;
    }

    /**
     * @param AlterProviderInterface|AlterClosure $provider
     *
     * @return $this
     */
    public function addAlterProvider(AlterProviderInterface|callable $provider): self
    {
        $this->alter[] = $provider;

        return $this;
    }

    /**
     * @param VisitProviderInterface|VisitClosure $provider
     *
     * @return $this
     */
    public function addVisitProvider(VisitProviderInterface|callable $provider): self
    {
        $this->visit[] = $provider;

        return $this;
    }

    public function build(): TreeMapper
    {
        $builder = new MapperBuilder();
        $builder = $builder->withCacheDir($this->cacheDir);

        foreach ($this->infer as $interfaceName => $infer) {
            $builder = $builder->infer($interfaceName, $infer);
        }

        foreach ($this->bind as $bind) {
            $builder = $builder->bind($bind);
        }

        foreach ($this->alter as $alter) {
            $builder = $builder->alter($alter);
        }

        foreach ($this->visit as $visit) {
            $builder = $builder->visit($visit);
        }

        return $builder->mapper();
    }
}
