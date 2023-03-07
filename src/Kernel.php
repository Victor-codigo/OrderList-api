<?php

namespace App;

use Common\Adapter\Compiler\KernelCustom;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        $environment = KernelCustom::changeEnvironmentByRequestQuery($environment);

        parent::__construct($environment, $debug);
    }

    protected function build(ContainerBuilder $container): void
    {
        KernelCustom::eventSubscribersAutoWire($container);
    }
}
