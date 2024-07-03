<?php

declare(strict_types=1);

namespace Common;

use Override;
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

    #[Override]
    protected function build(ContainerBuilder $container): void
    {
        KernelCustom::eventSubscribersAutoWire($container);
    }
}
