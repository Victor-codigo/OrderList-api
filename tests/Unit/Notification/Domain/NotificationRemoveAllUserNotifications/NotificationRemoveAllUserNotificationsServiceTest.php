<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationRemoveAllUserNotifications;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsDto;
use Notification\Domain\Service\NotificationRemoveAllUserNotifications\NotificationRemoveAllUserNotificationsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationRemoveAllUserNotificationsServiceTest extends TestCase
{
    private NotificationRemoveAllUserNotificationsService $object;
    private MockObject|NotificationRepositoryInterface $notificationRepository;
    private MockObject|PaginatorInterface $notificationPaginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->notificationPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new NotificationRemoveAllUserNotificationsService(
            $this->notificationRepository
        );
    }

    private function getUserNotifications(): array
    {
        return [
            Notification::fromPrimitives(
                'notification 1 id',
                'user id 1',
                NOTIFICATION_TYPE::GROUP_CREATED,
                []
            ),
            Notification::fromPrimitives(
                'notification 2 id',
                'user id 1',
                NOTIFICATION_TYPE::GROUP_REMOVED,
                []
            ),
            Notification::fromPrimitives(
                'notification 3 id',
                'user id 1',
                NOTIFICATION_TYPE::GROUP_USER_ADDED,
                []
            ),
            Notification::fromPrimitives(
                'notification 4 id',
                'user id 1',
                NOTIFICATION_TYPE::GROUP_USER_SET_AS_ADMIN,
                []
            ),
        ];
    }

    private function getNotificationsId(array $notifications): array
    {
        return array_map(
            fn (Notification $notification) => $notification->getId(),
            $notifications
        );
    }

    /** @test */
    public function itShouldRemoveAllUserNotifications(): void
    {
        $userNotifications = $this->getUserNotifications();
        $userNotificationsId = $this->getNotificationsId($userNotifications);
        $input = new NotificationRemoveAllUserNotificationsDto(
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($input->userId)
            ->willReturn($this->notificationPaginator);

        $this->notificationRepository
            ->expects($this->once())
            ->method('remove')
            ->with($userNotifications);

        $this->notificationPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($userNotifications));

        $return = $this->object->__invoke($input);

        $this->assertEquals($userNotificationsId, $return);
    }

    /** @test */
    public function itShouldFailNoUserNotificationsFound(): void
    {
        $input = new NotificationRemoveAllUserNotificationsDto(
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($input->userId)
            ->willThrowException(new DBNotFoundException());

        $this->notificationRepository
            ->expects($this->never())
            ->method('remove');

        $this->notificationPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingNotifications(): void
    {
        $userNotifications = $this->getUserNotifications();
        $input = new NotificationRemoveAllUserNotificationsDto(
            ValueObjectFactory::createIdentifier('user id')
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($input->userId)
            ->willReturn($this->notificationPaginator);

        $this->notificationRepository
            ->expects($this->once())
            ->method('remove')
            ->with($userNotifications)
            ->willThrowException(new DBConnectionException());

        $this->notificationPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($userNotifications));

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
