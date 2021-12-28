<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\Providers;

use CuyZ\Valinor\Mapper\Tree\Shell;

interface InferProviderInterface
{
    /** @return class-string */
    public function getInterfaceName(): string;

    /** @return class-string */
    public function __invoke(Shell $shell): string;
}
