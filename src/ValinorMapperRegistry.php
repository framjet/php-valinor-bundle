<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle;

use CuyZ\Valinor\Mapper\TreeMapper;
use InvalidArgumentException;

use function sprintf;

class ValinorMapperRegistry implements TreeMapper
{
    /** @var array<string, TreeMapper> */
    private array $mappers = [];

    /**
     * @psalm-param array<string, TreeMapper>   $mappers
     * @psalm-param array<class-string, string> $classToMapper
     */
    public function __construct(
        iterable $mappers,
        private array $classToMapper = [],
    ) {
        foreach ($mappers as $name => $mapper) {
            $this->mappers[$name] = $mapper;
        }
    }

    /**
     * @param class-string<T> $signature
     *
     * @template T of object
     */
    public function getMapperFor(string $signature): TreeMapper
    {
        $name = $this->classToMapper[$signature] ?? null;
        if ($name === null) {
            return $this->getDefaultMapper();
        }

        return $this->mappers[$name] ?? throw new InvalidArgumentException(
            sprintf(
                'Valinor mapper with name "%s" does not exist',
                $name
            )
        );
    }

    public function getDefaultMapper(): TreeMapper
    {
        return $this->mappers['default'] ?? throw new InvalidArgumentException('Valinor default mapper not found');
    }

    /** @inheritDoc */
    public function map(string $signature, $source): object
    {
        return $this->getMapperFor($signature)
                    ->map($signature, $source);
    }
}
