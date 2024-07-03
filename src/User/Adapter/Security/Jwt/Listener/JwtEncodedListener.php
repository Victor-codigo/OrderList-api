<?php

declare(strict_types=1);

namespace User\Adapter\Security\Jwt\Listener;

use Common\Domain\Validation\User\USER_ROLES;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsEventListener('lexik_jwt_authentication.on_jwt_encoded', 'onJwtEncoded')]
class JwtEncodedListener
{
    public function __construct(
        private Security $security,
        private RequestStack $request
    ) {
    }

    public function onJwtEncoded(JWTEncodedEvent $event): void
    {
        $user = $this->security->getUser();

        if (!$this->isUserFirstLogin($user)) {
            return;
        }

        $token = $event->getJWTString();
        $request = $this->request->getCurrentRequest();
        $request->headers->add(['Authorization' => "Bearer {$token}"]);
    }

    private function isUserFirstLogin(UserInterface $user): bool
    {
        $userRolFirstLogin = array_filter($user->getRoles(), fn (string $rol): bool => $rol === USER_ROLES::USER_FIRST_LOGIN->value);

        return empty($userRolFirstLogin) ? false : true;
    }
}
