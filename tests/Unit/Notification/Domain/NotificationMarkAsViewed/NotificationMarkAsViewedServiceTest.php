<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationMarkAsViewed;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedDto;
use Notification\Domain\Service\NotificationMarkAsViewed\NotificationMarkAsViewedService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationMarkAsViewedServiceTest extends TestCase
{
    private NotificationMarkAsViewedService $object;
    private MockObject|NotificationRepositoryInterface $notificationRepository;
    private MockObject|PaginatorInterface $paginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new NotificationMarkAsViewedService($this->notificationRepository);
    }

    /**
     * @return Notification[]
     */
    private function getNotifications(): array
    {
        return [
            Notification::fromPrimitives(
                'notification id 1',
                'user id 1',
                NOTIFICATION_TYPE::GROUP_USER_ADDED,
                [],
            ),
            Notification::fromPrimitives(
                'notification id 2',
                'user id 2',
                NOTIFICATION_TYPE::GROUP_REMOVED,
                [],
            ),
            Notification::fromPrimitives(
                'notification id 3',
                'user id 3',
                NOTIFICATION_TYPE::USER_EMAIL_CHANGED,
                [],
            ),
        ];
    }

    private function assertNotificationsAreOk(array $notificationsExpected, array $notificationsActual): void
    {
        $this->assertCount(count($notificationsExpected), $notificationsActual);

        foreach ($notificationsActual as $notification) {
            $this->assertInstanceOf(Notification::class, $notification);
            $this->assertTrue($notification->getViewed());
        }
    }

    /** @test */
    public function itShouldMarkNotificationsAsViewed(): void
    {
        $notifications = $this->getNotifications();
        $input = new NotificationMarkAsViewedDto([
            ValueObjectFactory::createIdentifier('notification id 1'),
            ValueObjectFactory::createIdentifier('notification id 2'),
            ValueObjectFactory::createIdentifier('notification id 3'),
        ]);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationsByIdOrFail')
            ->with($input->notificationId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 100);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($notifications));

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($notifications);

        $return = $this->object->__invoke($input);

        $this->assertNotificationsAreOk($notifications, $return);
    }

    /** @test */
    public function itShouldFailMarkingNotificationsAsViewedNotificationsNotFound(): void
    {
        $input = new NotificationMarkAsViewedDto([
            ValueObjectFactory::createIdentifier('notification id 1'),
            ValueObjectFactory::createIdentifier('notification id 2'),
            ValueObjectFactory::createIdentifier('notification id 3'),
        ]);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationsByIdOrFail')
            ->with($input->notificationId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->notificationRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailMarkingNotificationsAsViewedSaveException(): void
    {
        $notifications = $this->getNotifications();
        $input = new NotificationMarkAsViewedDto([
            ValueObjectFactory::createIdentifier('notification id 1'),
            ValueObjectFactory::createIdentifier('notification id 2'),
            ValueObjectFactory::createIdentifier('notification id 3'),
        ]);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationsByIdOrFail')
            ->with($input->notificationId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 100);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($notifications));

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($notifications)
            ->willThrowException(new DBUniqueConstraintException());

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->__invoke($input);
    }
}
