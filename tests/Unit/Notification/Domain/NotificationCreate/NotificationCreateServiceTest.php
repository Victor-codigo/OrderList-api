<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationCreate;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
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

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->object = new NotificationCreateService($this->notificationRepository);
    }

    private function getNotificationData(): NotificationData
    {
        return new NotificationData([
            'group_name' => 'GROUP NAME',
            'user_name' => 'USER NAME',
        ]);
    }

    #[Test]
    public function itShouldCreateANotification(): void
    {
        $input = new NotificationCreateDto(
            [ValueObjectFactory::createIdentifier('user id')],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::GROUP_USER_ADDED),
            $this->getNotificationData()
        );
        $notificationId = 'notification id';

        $this->notificationRepository
            ->expects($this->exactly(count($input->usersId)))
            ->method('generateId')
            ->willReturn($notificationId);

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $notification) use ($input, $notificationId): bool {
                $this->assertContainsOnlyInstancesOf(Notification::class, $notification);

                foreach ($input->usersId as $key => $userId) {
                    $this->assertEquals($notification[$key]->getId()->getValue(), $notificationId);
                    $this->assertContainsEquals($notification[$key]->getUserId(), $input->usersId);
                    $this->assertEquals($notification[$key]->getType(), $input->notificationType);
                    $this->assertEquals($notification[$key]->getData(), $input->notificationData);
                }

                return true;
            }));

        $return = $this->object->__invoke($input);

        $this->assertContainsOnlyInstancesOf(Notification::class, $return);

        foreach ($input->usersId as $key => $userId) {
            $this->assertContainsEquals($return[$key]->getUserId(), $input->usersId);
            $this->assertEquals($return[$key]->getType(), $input->notificationType);
            $this->assertEquals($return[$key]->getData(), $input->notificationData);
        }
    }

    #[Test]
    public function itShouldCreateManyNotifications(): void
    {
        $input = new NotificationCreateDto(
            [
                ValueObjectFactory::createIdentifier('user id 1'),
                ValueObjectFactory::createIdentifier('user id 2'),
                ValueObjectFactory::createIdentifier('user id 3'),
            ],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::USER_REGISTERED),
            $this->getNotificationData()
        );
        $notificationId = 'notification id';

        $this->notificationRepository
            ->expects($this->exactly(count($input->usersId)))
            ->method('generateId')
            ->willReturn($notificationId);

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $notification) use ($input, $notificationId): bool {
                $this->assertContainsOnlyInstancesOf(Notification::class, $notification);

                foreach ($input->usersId as $key => $userId) {
                    $this->assertEquals($notification[$key]->getId()->getValue(), $notificationId);
                    $this->assertContainsEquals($notification[$key]->getUserId(), $input->usersId);
                    $this->assertEquals($notification[$key]->getType(), $input->notificationType);
                    $this->assertEquals($notification[$key]->getData(), $input->notificationData);
                }

                return true;
            }));

        $return = $this->object->__invoke($input);

        $this->assertContainsOnlyInstancesOf(Notification::class, $return);

        foreach ($input->usersId as $key => $userId) {
            $this->assertContainsEquals($return[$key]->getUserId(), $input->usersId);
            $this->assertEquals($return[$key]->getType(), $input->notificationType);
            $this->assertEquals($return[$key]->getData(), $input->notificationData);
        }
    }

    #[Test]
    public function itShouldFailThereIsAlreadyANotificationWithTheSameId(): void
    {
        $input = new NotificationCreateDto(
            [ValueObjectFactory::createIdentifier('user id')],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::GROUP_USER_ADDED),
            $this->getNotificationData()
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailDatabaseError(): void
    {
        $input = new NotificationCreateDto(
            [ValueObjectFactory::createIdentifier('user id')],
            ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::GROUP_USER_ADDED),
            $this->getNotificationData()
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
