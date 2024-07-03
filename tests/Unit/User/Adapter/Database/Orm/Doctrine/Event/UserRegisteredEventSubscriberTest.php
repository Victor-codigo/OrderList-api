<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Database\Orm\Doctrine\Event;

use Override;
use stdClass;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Adapter\Database\Orm\Doctrine\Event\UserRegisteredEventSubscriber;
use User\Domain\Event\UserPreRegistered\UserPreRegisteredEvent;
use User\Domain\Model\User;

class UserRegisteredEventSubscriberTest extends TestCase
{
    private UserRegisteredEventSubscriber $object;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->user = $this->createMock(User::class);
        $this->object = new UserRegisteredEventSubscriber();
    }

    private function createUserPreRegisteredEvent(string|null $userRegisterEmailConfirmationUrl): UserPreRegisteredEvent
    {
        return new UserPreRegisteredEvent(
            ValueObjectFactory::createIdentifier('userId'),
            ValueObjectFactory::createEmail('userEmail'),
            ValueObjectFactory::createUrl($userRegisterEmailConfirmationUrl),
        );
    }

    /** @test */
    public function itShouldNotDispatchUserPreRegisteredEvent(): void
    {
        $event = new PostPersistEventArgs(new stdClass(), $this->entityManager);

        $this->user
            ->expects($this->never())
            ->method('getUserPreRegisteredEventData');

        $this->object->postPersist($event);
    }

    /** @test */
    public function itShouldDispatchUserPreRegisteredEventPreRegisteredDataIsNull(): void
    {
        $event = new PostPersistEventArgs($this->user, $this->entityManager);
        $userPreRegisteredEventData = $this->createUserPreRegisteredEvent(null);

        $this->user
            ->expects($this->once())
            ->method('getUserPreRegisteredEventData')
            ->willReturn(null);

        $this->user
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userPreRegisteredEventData->id);

        $this->user
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn($userPreRegisteredEventData->emailTo);

        $this->user
            ->expects($this->once())
            ->method('eventDispatchRegister')
            ->with($this->callback(function (UserPreRegisteredEvent $event) use ($userPreRegisteredEventData) {
                $this->assertEquals($userPreRegisteredEventData->id, $event->id);
                $this->assertEquals($userPreRegisteredEventData->emailTo, $event->emailTo);
                $this->assertEquals($userPreRegisteredEventData->userRegisterEmailConfirmationUrl, $event->userRegisterEmailConfirmationUrl);

                return true;
            }));

        $this->object->postPersist($event);
    }

    /** @test */
    public function itShouldDispatchUserPreRegisteredEventPreRegisteredDataIsNotNull(): void
    {
        $event = new PostPersistEventArgs($this->user, $this->entityManager);
        $userPreRegisteredEventData = $this->createUserPreRegisteredEvent(null);

        $this->user
            ->expects($this->once())
            ->method('getUserPreRegisteredEventData')
            ->willReturn($userPreRegisteredEventData);

        $this->user
            ->expects($this->never())
            ->method('getId');

        $this->user
            ->expects($this->never())
            ->method('getEmail');

        $this->user
            ->expects($this->once())
            ->method('eventDispatchRegister')
            ->with($this->callback(function (UserPreRegisteredEvent $event) use ($userPreRegisteredEventData) {
                $this->assertEquals($userPreRegisteredEventData->id, $event->id);
                $this->assertEquals($userPreRegisteredEventData->emailTo, $event->emailTo);
                $this->assertEquals($userPreRegisteredEventData->userRegisterEmailConfirmationUrl, $event->userRegisterEmailConfirmationUrl);

                return true;
            }));

        $this->object->postPersist($event);
    }
}
