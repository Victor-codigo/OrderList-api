<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Event;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use User\Domain\Event\UserPreRegistered\UserPreRegisteredEvent;
use User\Domain\Model\User;

#[AsDoctrineListener(Events::postPersist)]
class UserRegisteredEventSubscriber
{
    public function postPersist(PostPersistEventArgs $event): void
    {
        /** @var User $user */
        $user = $event->getObject();

        if (!$user instanceof User) {
            return;
        }

        $userPreRegisteredEvent = $user->getUserPreRegisteredEventData();

        if (null === $userPreRegisteredEvent) {
            $userPreRegisteredEvent = $this->createUserPreRegisteredEvent(
                $user->getId(),
                $user->getEmail(),
                ValueObjectFactory::createUrl(null)
            );
        }

        $user->eventDispatchRegister($userPreRegisteredEvent);
    }

    private function createUserPreRegisteredEvent(Identifier $userId, Email $userEmail, Url $userRegisterEmailConfirmationUrl): UserPreRegisteredEvent
    {
        return new UserPreRegisteredEvent($userId, $userEmail, $userRegisterEmailConfirmationUrl);
    }
}
