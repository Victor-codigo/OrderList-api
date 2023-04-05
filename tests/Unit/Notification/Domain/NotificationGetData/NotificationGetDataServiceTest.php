<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationGetData\Dto\NotificationGetDataDto;
use Notification\Domain\Service\NotificationGetData\NotificationGetDataService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationGetDataServiceTest extends TestCase
{
    private const NOTIFICATION_ID_1 = '2d208936-a7e9-32c1-963f-0df7f57ae463';
    private const NOTIFICATION_ID_2 = '38dac117-2d4f-4057-8bc6-c972b5f439c6';
    private const NOTIFICATION_ID_3 = '79a674c7-e109-3094-b8d5-c19cc00f5519';

    private NotificationGetDataService $object;
    private MockObject|NotificationRepositoryInterface $notificationRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new NotificationGetDataService($this->notificationRepository);
    }

    /**
     * @return Notification[]
     */
    private function getNotifications(): \Iterator
    {
        return new \ArrayIterator([
            Notification::fromPrimitives(self::NOTIFICATION_ID_1, 'user id 1', NOTIFICATION_TYPE::GROUP_USER_ADDED),
            Notification::fromPrimitives(self::NOTIFICATION_ID_2, 'user id 2', NOTIFICATION_TYPE::USER_REGISTERED),
            Notification::fromPrimitives(self::NOTIFICATION_ID_3, 'user id 3', NOTIFICATION_TYPE::GROUP_USER_ADDED),
        ]);
    }

    /** @test */
    public function itShouldGetNotificationData(): void
    {
        $notifications = $this->getNotifications();
        $notificationsIds = array_map(fn (Notification $notification) => $notification->getId(), iterator_to_array($notifications));
        $notificationsUserIds = array_map(fn (Notification $notification) => $notification->getUserId(), iterator_to_array($notifications));
        $notificationsTypes = array_map(fn (Notification $notification) => $notification->getType(), iterator_to_array($notifications));
        $notificationsViewed = array_map(fn (Notification $notification) => $notification->getViewed(), iterator_to_array($notifications));

        $page = ValueObjectFactory::createPaginatorPage(1);
        $pageItems = ValueObjectFactory::createPaginatorPageItems(10);
        $userId = ValueObjectFactory::createIdentifier('user id');
        $input = new NotificationGetDataDto($userId, $page, $pageItems);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($notifications);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($userId)
            ->willReturn($this->paginator);

        $return = $this->object->__invoke($input);

        $this->assertCount(count($notifications), $return);

        foreach ($return as $notificationData) {
            $this->assertArrayHasKey('id', $notificationData);
            $this->assertArrayHasKey('user_id', $notificationData);
            $this->assertArrayHasKey('type', $notificationData);
            $this->assertArrayHasKey('viewed', $notificationData);
            $this->assertArrayHasKey('created_on', $notificationData);

            $this->assertContainsEquals($notificationData['id'], $notificationsIds);
            $this->assertContainsEquals($notificationData['user_id'], $notificationsUserIds);
            $this->assertContainsEquals($notificationData['type'], $notificationsTypes);
            $this->assertContainsEquals($notificationData['viewed'], $notificationsViewed);
        }
    }

    /** @test */
    public function itShouldFailGettingNotificationDataNotificationsNotFound(): void
    {
        $page = ValueObjectFactory::createPaginatorPage(1);
        $pageItems = ValueObjectFactory::createPaginatorPageItems(10);
        $userId = ValueObjectFactory::createIdentifier('user id');
        $input = new NotificationGetDataDto($userId, $page, $pageItems);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($userId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
