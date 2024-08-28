<?php

declare(strict_types=1);

namespace Common\Adapter\Compiler;

use Common\Adapter\Compiler\RegisterEventDomain\RegisterEventDomainSubscribers;
use Common\Domain\Event\EventDomainSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

class KernelCustom
{
    public function __construct(
        private Dotenv $dotEnv
    ) {
    }

    public function eventSubscribersAutoWire(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoConfiguration(EventDomainSubscriberInterface::class)
            ->addTag(RegisterEventDomainSubscribers::EVENT_DOMAIN_SUBSCRIBER_TAG);

        $container->addCompilerPass(new RegisterEventDomainSubscribers());
    }

    /**
     * @throws PathException
     * @throws FormatException
     */
    public function changeEnvironmentByRequestQuery(mixed $environment, string $projectDir): string
    {
        if ('dev' !== $environment) {
            return $environment;
        }

        if (!isset($_REQUEST['env']) || !is_string($_REQUEST['env'])) {
            return $environment;
        }

        $this->dotEnv->loadEnv(
            "{$projectDir}/.env.{$_REQUEST['env']}",
            $environment,
            'dev',
            ['test'],
            false
        );

        return $_ENV['APP_ENV'];
    }
}
