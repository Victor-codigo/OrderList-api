<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Fixtures;

use Common\Domain\Event\EventDomainSubscriberInterface;

class CustomEventSubscriberWithManyMethods implements EventDomainSubscriberInterface
{
    public function handler()
    {
    }

    public function execute()
    {
    }

    public function exe()
    {
    }

    public function __invoke()
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            CustomEvent::class,
            CustomEvent::class => [
                ['handler'],
                ['handler', 5],
                ['execute', 10],
                ['exe', 20],
            ],
        ];
    }
}
