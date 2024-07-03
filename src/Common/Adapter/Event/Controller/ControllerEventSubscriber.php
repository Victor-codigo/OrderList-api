<?php

declare(strict_types=1);

namespace Common\Adapter\Event\Controller;

use Common\Adapter\Http\TryoutPermissions\Exception\TryoutUserRoutePermissionsException;
use Common\Adapter\Http\TryoutPermissions\TryoutUserRoutePermissionsValidation;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TryoutUserRoutePermissionsValidation $tryoutUserPermissionsValidation,
        private Security $security
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => ['__invoke', 101]];
    }

    /**
     * @throws TryoutUserRoutePermissionsException
     */
    public function __invoke(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        $this->tryoutUserValidation($request);
    }

    /**
     * @throws TryoutUserRoutePermissionsException
     */
    private function tryoutUserValidation(Request $request): void
    {
        /** @var UserSharedSymfonyAdapter $userAdapterSymfony */
        $userAdapterSymfony = $this->security->getUser();

        if (null === $userAdapterSymfony) {
            return;
        }

        $this->tryoutUserPermissionsValidation->__invoke(
            $userAdapterSymfony->getUser()->getId(),
            $request->attributes->get('_route')
        );
    }
}
