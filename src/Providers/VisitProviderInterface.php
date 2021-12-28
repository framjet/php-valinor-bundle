<?php

declare(strict_types=1);

namespace FramJet\Packages\ValinorBundle\Providers;

use CuyZ\Valinor\Mapper\Tree\Node;

interface VisitProviderInterface
{
    public function __invoke(Node $node): void;
}
