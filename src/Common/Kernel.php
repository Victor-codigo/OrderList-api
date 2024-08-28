<?php

declare(strict_types=1);

namespace Common;

use Common\Adapter\Compiler\KernelCustom;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private KernelCustom $kernelCustom;

    public function __construct(string $environment, bool $debug)
    {
        $this->kernelCustom = $this->createKernelCustom();

        try {
            $environment = $this->kernelCustom->changeEnvironmentByRequestQuery($environment, $this->getProjectDir());
        } catch (\Throwable $th) {
            // some stuff
        }

        parent::__construct($environment, $debug);
    }

    #[\Override]
    protected function build(ContainerBuilder $container): void
    {
        $this->kernelCustom->eventSubscribersAutoWire($container);
    }

    private function createKernelCustom(): KernelCustom
    {
        return new KernelCustom(new Dotenv());
    }
}
