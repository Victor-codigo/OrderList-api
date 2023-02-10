<?php

declare(strict_types=1);

namespace Common\Adapter\Compiler;

use Common\Adapter\Compiler\RegisterEventDomain\RegisterEventDomainSubscribers;
use Common\Domain\Event\EventDomainSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KernelCustom
{
    public static function eventSubscribersAutoWire(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(EventDomainSubscriberInterface::class)
            ->addTag(RegisterEventDomainSubscribers::EVENT_DOMAIN_SUBSCRIBER_TAG);

        $container->addCompilerPass(new RegisterEventDomainSubscribers());
    }

    public static function changeEnviromentByRequestQuery($environment): string
    {
        if ('dev' !== $environment) {
            return $environment;
        }

        if (!isset($_REQUEST['env'])) {
            return $environment;
        }

        return match ($_REQUEST['env']) {
            'test' => 'test',
            default => $environment
        };
    }
}
