<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationRemove\Dto\NotificationRemoveDto;
use Notification\Domain\Service\NotificationRemove\NotificationRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationRemoveServiceTest extends TestCase
{
    private const string NOTIFICATION_ID_1 = '2d208936-a7e9-32c1-963f-0df7f57ae463';
    private const string NOTIFICATION_ID_2 = '38dac117-2d4f-4057-8bc6-c972b5f439c6';
    private const string NOTIFICATION_ID_3 = '79a674c7-e109-3094-b8d5-c19cc00f5519';

    private NotificationRemoveService $object;
    private MockObject|NotificationRepositoryInterface $notificationRepository;
    private MockObject|PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new NotificationRemoveService($this->notificationRepository);
    }

    /**
     * @return Notification[]
     */
    private function getNotifications(): \Iterator
    {
        return new \ArrayIterator([
            Notification::fromPrimitives(self::NOTIFICATION_ID_1, 'user id 1', NOTIFICATION_TYPE::GROUP_USER_ADDED, []),
            Notification::fromPrimitives(self::NOTIFICATION_ID_2, 'user id 2', NOTIFICATION_TYPE::USER_REGISTERED, []),
            Notification::fromPrimitives(self::NOTIFICATION_ID_3, 'user id 3', NOTIFICATION_TYPE::GROUP_USER_ADDED, []),
        ]);
    }

    /** @test */
    public function itShouldRemoveNotifications(): void
    {
        $notifications = $this->getNotifications();
        $notificationsIds = array_map(fn (Notification $notification): Identifier => $notification->getId(), iterator_to_array($notifications));
        $input = new NotificationRemoveDto($notificationsIds);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($notifications);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationsByIdOrFail')
            ->with($notificationsIds)
            ->willReturn($this->paginator);

        $this->notificationRepository
            ->expects($this->once())
            ->method('remove')
            ->with(iterator_to_array($notifications));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($notifications), $return);

        foreach ($return as $notification) {
            $this->assertContainsEquals($notification->getId(), $notificationsIds);
        }
    }

    /** @test */
    public function itShouldFailRemovingNotificationsNotFound(): void
    {
        $notifications = $this->getNotifications();
        $notificationsIds = array_map(fn (Notification $notification): Identifier => $notification->getId(), iterator_to_array($notifications));
        $input = new NotificationRemoveDto($notificationsIds);

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationsByIdOrFail')
            ->with($notificationsIds)
            ->willThrowException(new DBNotFoundException());

        $this->notificationRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingDatabaseErrorConnection(): void
    {
        $notifications = $this->getNotifications();
        $notificationsIds = array_map(fn (Notification $notification): Identifier => $notification->getId(), iterator_to_array($notifications));
        $input = new NotificationRemoveDto($notificationsIds);

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationsByIdOrFail')
            ->with($notificationsIds)
            ->willThrowException(new DBConnectionException());

        $this->notificationRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
