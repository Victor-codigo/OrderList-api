<?php

namespace App;

use Common\Adapter\Compiler\KernelCustom;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $this->environment = KernelCustom::changeEnviromentByRequestQuery($this->environment);
        KernelCustom::eventSubscribersAutoWire($container);
    }
}
