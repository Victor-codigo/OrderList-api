<?php

declare(strict_types=1);

namespace User\Adapter\Security\Jwt\Listener;

use Common\Domain\Ports\Event\EventDispatcherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Event\UserLogin\UserLoginEvent;
use User\Domain\Model\User;

#[AsEventListener('lexik_jwt_authentication.on_authentication_success', 'onAuthenticationSuccess')]
class JwtAuthenticatedListener
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Security $security
    ) {
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        if (!$event->getUser() instanceof UserInterface) {
            return;
        }

        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        $this->eventDispatcher->dispatch(
            $this->createUserLoginEvent($userAdapter->getUser())
        );
    }

    private function createUserLoginEvent(User $user): UserLoginEvent
    {
        return new UserLoginEvent($user);
    }
}
