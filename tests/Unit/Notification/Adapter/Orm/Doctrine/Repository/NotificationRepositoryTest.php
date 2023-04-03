<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Adapter\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Notification\Adapter\Database\Orm\Doctrine\Repository\NotificationRepository;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class NotificationRepositoryTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private const NOTIFICATION_ID = '5f4ef311-6942-4c73-882e-c4fb1dbff7bc';
    private const NOTIFICATION_2_ID = '5e5c28e1-0f72-4e86-999f-743c5174d5d2';
    private const NOTIFICATION_REPEATED_ID = '84a08f7c-30a6-4bd5-8e5b-b2d49948e72c';
    private const NOTIFICATION_USER_ID = 'b92f6cbe-f995-47b5-b54b-bf2218d6cf26';

    private NotificationRepository $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Notification::class);
    }

    /** @test */
    public function itShouldSaveTheNotification(): void
    {
        $notification = Notification::fromPrimitives(
            self::NOTIFICATION_ID,
            self::NOTIFICATION_USER_ID,
            NOTIFICATION_TYPE::USER_REGISTERED
        );

        $this->object->save([$notification]);

        /** @var Notification $notificationSaved */
        $notificationSaved = $this->object->findBy(['id' => $notification->getId()])[0];

        $this->assertNotEmpty($notificationSaved);
        $this->assertEquals($notification->getId(), $notificationSaved->getId());
        $this->assertEquals($notification->getUserId(), $notificationSaved->getUserId());
        $this->assertEquals($notification->getType(), $notificationSaved->getType());
        $this->assertFalse($notificationSaved->getViewed());
    }

    /** @test */
    public function itShouldSaveManyNotifications(): void
    {
        $notifications = [
            Notification::fromPrimitives(
                self::NOTIFICATION_ID,
                self::NOTIFICATION_USER_ID,
                NOTIFICATION_TYPE::USER_REGISTERED
            ),
            Notification::fromPrimitives(
                self::NOTIFICATION_2_ID,
                self::NOTIFICATION_USER_ID,
                NOTIFICATION_TYPE::USER_REGISTERED
            ),
        ];
        $notificationsId = array_map(fn (Notification $notification) => $notification->getId(), $notifications);
        $notificationsUsersId = array_map(fn (Notification $notification) => $notification->getUserId(), $notifications);

        $this->object->save($notifications);

        /** @var Notification $notificationSaved */
        $notificationSaved = $this->object->findBy(['id' => $notificationsId]);

        $this->assertNotEmpty($notificationSaved);

        foreach ($notifications as $key => $notification) {
            $this->assertContainsEquals($notificationSaved[$key]->getId(), $notificationsId);
            $this->assertContainsEquals($notificationSaved[$key]->getUserId(), $notificationsUsersId);
            $this->assertEquals($notification->getType(), $notificationSaved[$key]->getType());
            $this->assertFalse($notificationSaved[$key]->getViewed());
        }
    }

    /** @test */
    public function itShouldFailIdUniqueConstraint(): void
    {
        $notification = Notification::fromPrimitives(
            self::NOTIFICATION_REPEATED_ID,
            self::NOTIFICATION_USER_ID,
            NOTIFICATION_TYPE::USER_REGISTERED
        );

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->save([$notification]);
    }

    /** @test */
    public function itShouldFailDataBaseError(): void
    {
        $notification = Notification::fromPrimitives(
            self::NOTIFICATION_REPEATED_ID,
            self::NOTIFICATION_USER_ID,
            NOTIFICATION_TYPE::USER_REGISTERED
        );

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
        ->expects($this->once())
        ->method('flush')
        ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);

        $this->expectException(DBConnectionException::class);
        $this->object->save([$notification]);
    }
}
