<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ValinorMapped
{
    public function __construct(public string $mapperName = 'default')
    {
    }
}
