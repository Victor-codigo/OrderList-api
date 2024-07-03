<?php

declare(strict_types=1);

namespace Common\Adapter\Compiler\RegisterEventDomain;

use Common\Adapter\Event\EventDispatcherSymfonyAdapter;
use Common\Domain\Event\EventDomainSubscriberInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RegisterEventDomainSubscribers implements CompilerPassInterface
{
    private const string EVENT_DISPATCHER_SERVICE = 'event_dispatcher';
    public const string EVENT_DOMAIN_SUBSCRIBER_TAG = 'common.event_subscriber';

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $eventDispatcherService = $container->get(static::EVENT_DISPATCHER_SERVICE);
        $definition = $container->findDefinition(EventDispatcherSymfonyAdapter::class);
        $eventDomainSubscribers = $container->findTaggedServiceIds(static::EVENT_DOMAIN_SUBSCRIBER_TAG, true);

        foreach ($eventDomainSubscribers as $className => $attributes) {
            if (EventSubscriberLoader::class === $className) {
                continue;
            }

            $this->validEventDomainSubscriber($container, $className);

            $eventSubscriberLoader = $this->getEventSubscriberLoader($eventDispatcherService);
            $eventSubscriberLoader->setSubscriber($className);
            $eventSubscriberLoader->addSubscriber($eventSubscriberLoader);

            $this->addListeners($eventSubscriberLoader, $className, $definition);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validEventDomainSubscriber(ContainerBuilder $container, string $className): void
    {
        $reflectionClass = $container->getReflectionClass($container->getDefinition($className)->getClass());

        if (null === $reflectionClass) {
            throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $className, $className));
        }

        if (!$reflectionClass->isSubclassOf(EventDomainSubscriberInterface::class)) {
            throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $className, EventDomainSubscriberInterface::class));
        }
    }

    private function addListeners(EventSubscriberLoader $extractingDispatcher, string $className, Definition $definition): void
    {
        foreach ($extractingDispatcher->getListeners() as $listener) {
            $listener[1] = [new ServiceClosureArgument(new Reference($className)), $listener[1]];
            $definition->addMethodCall('addListener', $listener);
        }
    }

    private function getEventSubscriberLoader(EventDispatcher $eventDispatcherService): EventSubscriberLoader
    {
        return new EventSubscriberLoader($eventDispatcherService);
    }
}
