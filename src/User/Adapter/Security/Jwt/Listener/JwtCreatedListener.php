<?php

declare(strict_types=1);

namespace User\Adapter\Security\Jwt\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use User\Domain\Port\User\UserInterface;

#[AsEventListener('lexik_jwt_authentication.on_jwt_created', 'onJWTCreated')]
class JwtCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $userAdapter = $event->getUser();

        if (!$userAdapter instanceof UserInterface) {
            return;
        }

        $payload = $event->getData();
        $payload['username'] = $userAdapter->getUser()->getId()->getValue();

        $event->setData($payload);
    }
}
