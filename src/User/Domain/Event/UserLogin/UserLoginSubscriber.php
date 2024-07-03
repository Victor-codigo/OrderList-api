<?php

declare(strict_types=1);

namespace User\Domain\Event\UserLogin;

use Override;
use Common\Domain\Event\EventDomainSubscriberInterface;
use User\Domain\Model\User;
use User\Domain\Service\UserFirstLogin\Dto\UserFirstLoginDto;
use User\Domain\Service\UserFirstLogin\UserFirstLoginService;

class UserLoginSubscriber implements EventDomainSubscriberInterface
{
    /**
     * @return array<string, array<string, int>>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            UserLoginEvent::class,
        ];
    }

    public function __construct(
        private UserFirstLoginService $userFirstLoginService
    ) {
    }

    public function __invoke(UserLoginEvent $event): void
    {
        $this->createUserGroup($event->user);
    }

    private function createUserGroup(User $user): void
    {
        $this->userFirstLoginService->__invoke(
            $this->createUserFirstLoginDto($user)
        );
    }

    private function createUserFirstLoginDto(User $user): UserFirstLoginDto
    {
        return new UserFirstLoginDto($user);
    }
}
