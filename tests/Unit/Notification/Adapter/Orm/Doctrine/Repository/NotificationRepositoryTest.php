<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Adapter\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Notification\Adapter\Database\Orm\Doctrine\Repository\NotificationRepository;
use Notification\Domain\Model\Notification;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;

class NotificationRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string NOTIFICATION_ID = '5f4ef311-6942-4c73-882e-c4fb1dbff7bc';
    private const string NOTIFICATION_2_ID = '5e5c28e1-0f72-4e86-999f-743c5174d5d2';
    private const string NOTIFICATION_REPEATED_ID = '84a08f7c-30a6-4bd5-8e5b-b2d49948e72c';
    private const string NOTIFICATION_USER_ID = 'b92f6cbe-f995-47b5-b54b-bf2218d6cf26';
    private const string NOTIFICATION_USER_ID_ACTIVE = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string NOTIFICATION_IN_DATABASE_1 = '84a08f7c-30a6-4bd5-8e5b-b2d49948e72c';
    private const string NOTIFICATION_IN_DATABASE_2 = '38dac117-2d4f-4057-8bc6-c972b5f439c6';
    private const string NOTIFICATION_IN_DATABASE_3 = 'f79ddff5-486b-4b5f-af64-b99fe9154fc1';

    private NotificationRepository $object;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Notification::class);
    }

    private function getNotificationData(): array
    {
        return [
            'data1' => 'value1',
            'data2' => 'value2',
        ];
    }

    /** @test */
    public function itShouldSaveTheNotification(): void
    {
        $notification = Notification::fromPrimitives(
            self::NOTIFICATION_ID,
            self::NOTIFICATION_USER_ID,
            NOTIFICATION_TYPE::USER_REGISTERED,
            $this->getNotificationData()
        );

        $this->object->save([$notification]);

        /** @var Notification $notificationSaved */
        $notificationSaved = $this->object->findBy(['id' => $notification->getId()])[0];

        $this->assertNotEmpty($notificationSaved);
        $this->assertEquals($notification->getId(), $notificationSaved->getId());
        $this->assertEquals($notification->getUserId(), $notificationSaved->getUserId());
        $this->assertEquals($notification->getType(), $notificationSaved->getType());
        $this->assertEquals($notification->getData(), $notificationSaved->getData());
        $this->assertFalse($notificationSaved->getViewed());
    }

    /** @test */
    public function itShouldSaveManyNotifications(): void
    {
        $notifications = [
            Notification::fromPrimitives(
                self::NOTIFICATION_ID,
                self::NOTIFICATION_USER_ID,
                NOTIFICATION_TYPE::USER_REGISTERED,
                $this->getNotificationData()
            ),
            Notification::fromPrimitives(
                self::NOTIFICATION_2_ID,
                self::NOTIFICATION_USER_ID,
                NOTIFICATION_TYPE::USER_REGISTERED,
                $this->getNotificationData()
            ),
        ];
        $notificationsId = array_map(fn (Notification $notification): Identifier => $notification->getId(), $notifications);
        $notificationsUsersId = array_map(fn (Notification $notification): Identifier => $notification->getUserId(), $notifications);

        $this->object->save($notifications);

        /** @var Notification $notificationSaved */
        $notificationSaved = $this->object->findBy(['id' => $notificationsId]);

        $this->assertNotEmpty($notificationSaved);

        foreach ($notifications as $key => $notification) {
            $this->assertContainsEquals($notificationSaved[$key]->getId(), $notificationsId);
            $this->assertContainsEquals($notificationSaved[$key]->getUserId(), $notificationsUsersId);
            $this->assertEquals($notification->getType(), $notificationSaved[$key]->getType());
            $this->assertEquals($notification->getData(), $notificationSaved[$key]->getData());
            $this->assertFalse($notificationSaved[$key]->getViewed());
        }
    }

    /** @test */
    public function itShouldFailIdUniqueConstraint(): void
    {
        $notification = Notification::fromPrimitives(
            self::NOTIFICATION_REPEATED_ID,
            self::NOTIFICATION_USER_ID,
            NOTIFICATION_TYPE::USER_REGISTERED,
            $this->getNotificationData()
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
            NOTIFICATION_TYPE::USER_REGISTERED,
            $this->getNotificationData()
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

    /** @test */
    public function itShouldGetNotificationsById(): void
    {
        $notificationsId = [
            ValueObjectFactory::createIdentifier(self::NOTIFICATION_IN_DATABASE_1),
            ValueObjectFactory::createIdentifier(self::NOTIFICATION_IN_DATABASE_2),
            ValueObjectFactory::createIdentifier(self::NOTIFICATION_IN_DATABASE_3),
        ];

        $return = $this->object->getNotificationsByIdOrFail($notificationsId);

        $this->assertCount(count($notificationsId), $return);

        /** @var Notification $notification */
        foreach ($return as $notification) {
            $this->assertContainsEquals($notification->getId(), $notificationsId);
        }
    }

    /** @test */
    public function itShouldFailGettingNotificationsByIdNotNotificationsFound(): void
    {
        $notificationsId = [
            ValueObjectFactory::createIdentifier('notification not exists'),
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->getNotificationsByIdOrFail($notificationsId);
    }

    /** @test */
    public function itShouldGetNotificationsByUserIdOrderedAsc(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::NOTIFICATION_USER_ID_ACTIVE);
        $return = $this->object->getNotificationByUserIdOrFail($userId);

        $notificationsExpected = $this->object->findBy(['userId' => $userId], ['createdOn' => 'DESC']);

        $this->assertCount(count($notificationsExpected), $return);

        foreach ($return as $key => $notification) {
            $this->assertEquals($notification, $notificationsExpected[$key]);
        }
    }

    /** @test */
    public function itShouldFailGettingNotificationsByUserIdNotFound(): void
    {
        $userId = ValueObjectFactory::createIdentifier('7499d138-b2a2-4b73-b9ac-0bdba054373b');

        $this->expectException(DBNotFoundException::class);
        $this->object->getNotificationByUserIdOrFail($userId);
    }
}
