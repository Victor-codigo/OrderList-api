<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationCreate\Dto\NotificationCreateDto;
use Notification\Domain\Service\NotificationCreate\NotificationCreateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationCreateServiceTest extends TestCase
{
    private NotificationCreateService $object;
    private MockObject|NotificationRepositoryInterface $notificationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->object = new NotificationCreateService($this->notificationRepository);
    }

    /** @test */
    public function itShouldCreateANotification(): void
    {
        $input = new NotificationCreateDto(
            [ValueObjectFactory::createIdentifier('user id')],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::GROUP_USER_ADDED)
        );
        $notificationId = 'notification id';

        $this->notificationRepository
            ->expects($this->exactly(count($input->usersId)))
            ->method('generateId')
            ->willReturn($notificationId);

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $notification) use ($input, $notificationId) {
                $this->assertContainsOnlyInstancesOf(Notification::class, $notification);

                foreach ($input->usersId as $key => $userId) {
                    $this->assertEquals($notification[$key]->getId()->getValue(), $notificationId);
                    $this->assertContainsEquals($notification[$key]->getUserId(), $input->usersId);
                    $this->assertEquals($notification[$key]->getType(), $input->notificationType);
                }

                return true;
            }));

        $return = $this->object->__invoke($input);

        $this->assertContainsOnlyInstancesOf(Notification::class, $return);

        foreach ($input->usersId as $key => $userId) {
            $this->assertContainsEquals($return[$key]->getUserId(), $input->usersId);
            $this->assertEquals($return[$key]->getType(), $input->notificationType);
        }
    }

    /** @test */
    public function itShouldCreateManyNotifications(): void
    {
        $input = new NotificationCreateDto(
            [
                ValueObjectFactory::createIdentifier('user id 1'),
                ValueObjectFactory::createIdentifier('user id 2'),
                ValueObjectFactory::createIdentifier('user id 3'),
            ],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::USER_REGISTERED)
        );
        $notificationId = 'notification id';

        $this->notificationRepository
            ->expects($this->exactly(count($input->usersId)))
            ->method('generateId')
            ->willReturn($notificationId);

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $notification) use ($input, $notificationId) {
                $this->assertContainsOnlyInstancesOf(Notification::class, $notification);

                foreach ($input->usersId as $key => $userId) {
                    $this->assertEquals($notification[$key]->getId()->getValue(), $notificationId);
                    $this->assertContainsEquals($notification[$key]->getUserId(), $input->usersId);
                    $this->assertEquals($notification[$key]->getType(), $input->notificationType);
                }

                return true;
            }));

        $return = $this->object->__invoke($input);

        $this->assertContainsOnlyInstancesOf(Notification::class, $return);

        foreach ($input->usersId as $key => $userId) {
            $this->assertContainsEquals($return[$key]->getUserId(), $input->usersId);
            $this->assertEquals($return[$key]->getType(), $input->notificationType);
        }
    }

    /** @test */
    public function itShouldFailThereIsAlreadyANotificationWithTheSameId(): void
    {
        $input = new NotificationCreateDto(
            [ValueObjectFactory::createIdentifier('user id')],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::GROUP_USER_ADDED)
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailDatabaseError(): void
    {
        $input = new NotificationCreateDto(
            [ValueObjectFactory::createIdentifier('user id')],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::GROUP_USER_ADDED)
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
