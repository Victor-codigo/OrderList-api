<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Domain\NotificationGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationGetData\Dto\NotificationGetDataDto;
use Notification\Domain\Service\NotificationGetData\NotificationGetDataService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationGetDataServiceTest extends TestCase
{
    private const string NOTIFICATION_ID_1 = '2d208936-a7e9-32c1-963f-0df7f57ae463';
    private const string NOTIFICATION_ID_2 = '38dac117-2d4f-4057-8bc6-c972b5f439c6';
    private const string NOTIFICATION_ID_3 = '79a674c7-e109-3094-b8d5-c19cc00f5519';
    private const string NOTIFICATION_ID_4 = '26884a81-c072-4af9-bbdb-6045d827b4ac';
    private const string NOTIFICATION_ID_5 = '10332278-54b9-4f2a-a307-ecf10cc49a2b';
    private const string NOTIFICATION_ID_6 = '0776d4da-edc9-4337-80d5-57e12ac51b17';
    private const string NOTIFICATION_ID_7 = 'a43d386d-c878-40bf-a9bf-0aedea922260';
    private const string NOTIFICATION_ID_8 = '67d5ab78-5f19-40f0-869c-d079f6983bb3';

    private const string TRANSLATOR_DOMAIN = 'Notifications';

    private NotificationGetDataService $object;
    private MockObject&NotificationRepositoryInterface $notificationRepository;
    /**
     * @var MockObject&PaginatorInterface<int, Notification>
     */
    private MockObject&PaginatorInterface $paginator;
    private MockObject&TranslatorInterface $translator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->object = new NotificationGetDataService($this->notificationRepository, $this->translator);
    }

    private function getNotifications(): \Iterator
    {
        $notificationGroupUserAdded = [
            'group_name' => 'GROUP NAME',
            'user_who_adds_you_name' => 'USER WHO ADDS YOU',
        ];

        $notificationGroupCreated = [
            'group_name' => 'GROUP NAME',
        ];

        $notificationGroupRemoved = [
            'group_name' => 'GROUP NAME',
        ];

        $notificationGroupUserRemoved = [
            'group_name' => 'GROUP NAME',
        ];

        $notificationUserRegistered = [
            'user_name' => 'USER NAME',
            'domain_name' => 'DOMAIN NAME',
        ];

        $notificationShareListOrdersCreated = [
            'list_orders_name' => 'USER NAME',
            'list_orders_id' => 'DOMAIN NAME',
        ];

        return new \ArrayIterator([
            Notification::fromPrimitives(self::NOTIFICATION_ID_1, 'user id 1', NOTIFICATION_TYPE::GROUP_CREATED, $notificationGroupCreated),
            Notification::fromPrimitives(self::NOTIFICATION_ID_2, 'user id 2', NOTIFICATION_TYPE::GROUP_REMOVED, $notificationGroupRemoved),
            Notification::fromPrimitives(self::NOTIFICATION_ID_3, 'user id 3', NOTIFICATION_TYPE::GROUP_USER_ADDED, $notificationGroupUserAdded),
            Notification::fromPrimitives(self::NOTIFICATION_ID_4, 'user id 4', NOTIFICATION_TYPE::GROUP_USER_REMOVED, $notificationGroupUserRemoved),

            Notification::fromPrimitives(self::NOTIFICATION_ID_5, 'user id 5', NOTIFICATION_TYPE::USER_EMAIL_CHANGED, []),
            Notification::fromPrimitives(self::NOTIFICATION_ID_6, 'user id 6', NOTIFICATION_TYPE::USER_PASSWORD_CHANGED, []),
            Notification::fromPrimitives(self::NOTIFICATION_ID_7, 'user id 7', NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER, []),
            Notification::fromPrimitives(self::NOTIFICATION_ID_8, 'user id 8', NOTIFICATION_TYPE::USER_REGISTERED, $notificationUserRegistered),

            Notification::fromPrimitives(self::NOTIFICATION_ID_8, 'user id 9', NOTIFICATION_TYPE::SHARE_LIST_ORDERS_CREATED, $notificationShareListOrdersCreated),
        ]);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function providerNotificationLanguage(): array
    {
        return [
            ['en'],
            ['es'],
        ];
    }

    #[DataProvider('providerNotificationLanguage')]
    #[Test]
    public function itShouldGetNotificationData(string $providerLang): void
    {
        $notifications = $this->getNotifications();
        $notificationsIds = array_map(fn (Notification $notification): ?string => $notification->getId()->getValue(), iterator_to_array($notifications));
        $notificationsUserIds = array_map(fn (Notification $notification): ?string => $notification->getUserId()->getValue(), iterator_to_array($notifications));
        $notificationsTypes = array_map(fn (Notification $notification): ?object => $notification->getType()->getValue(), iterator_to_array($notifications));
        $notificationsData = array_map(fn (Notification $notification): ?array => $notification->getData()->getValue(), iterator_to_array($notifications));
        $notificationsViewed = array_map(fn (Notification $notification): bool => $notification->getViewed(), iterator_to_array($notifications));

        $page = ValueObjectFactory::createPaginatorPage(1);
        $pageItems = ValueObjectFactory::createPaginatorPageItems(10);
        $userId = ValueObjectFactory::createIdentifier('user id');
        $lang = ValueObjectFactory::createLanguage($providerLang);
        $input = new NotificationGetDataDto($userId, $page, $pageItems, $lang);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($notifications);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($userId)
            ->willReturn($this->paginator);

        $this->translator
            ->expects($this->exactly(iterator_count($notifications)))
            ->method('translate')
            ->with($this->callback(function (string $placeholder) use ($notificationsTypes): bool {
                static $callNumber = 0;

                match ($notificationsTypes[$callNumber++]) {
                    NOTIFICATION_TYPE::GROUP_CREATED => $this->assertEquals('notification.group.created', $placeholder),
                    NOTIFICATION_TYPE::GROUP_REMOVED => $this->assertEquals('notification.group.removed', $placeholder),
                    NOTIFICATION_TYPE::GROUP_USER_ADDED => $this->assertEquals('notification.group.user_added', $placeholder),
                    NOTIFICATION_TYPE::GROUP_USER_REMOVED => $this->assertEquals('notification.group.user_removed', $placeholder),

                    NOTIFICATION_TYPE::USER_EMAIL_CHANGED => $this->assertEquals('notification.user.email_changed', $placeholder),
                    NOTIFICATION_TYPE::USER_PASSWORD_CHANGED => $this->assertEquals('notification.user.password_changed', $placeholder),
                    NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER => $this->assertEquals('notification.user.password_remembered', $placeholder),
                    NOTIFICATION_TYPE::USER_REGISTERED => $this->assertEquals('notification.user.registered', $placeholder),
                    NOTIFICATION_TYPE::SHARE_LIST_ORDERS_CREATED => $this->assertEquals('notification.share.list_orders_created', $placeholder),
                    default => throw new \LogicException('Not supporting this value'),
                };

                return true;
            }),
                $this->callback(function (array $data) use ($notificationsData): bool {
                    static $callNumber = 0;

                    $this->assertEquals($notificationsData[$callNumber++], $data);

                    return true;
                }),
                self::TRANSLATOR_DOMAIN,
                $lang->getValue()
            );

        $return = $this->object->__invoke($input);

        $this->assertCount(iterator_count($notifications), $return);

        foreach ($return as $notificationData) {
            $this->assertArrayHasKey('id', $notificationData);
            $this->assertArrayHasKey('user_id', $notificationData);
            $this->assertArrayHasKey('message', $notificationData);
            $this->assertArrayHasKey('viewed', $notificationData);
            $this->assertArrayHasKey('created_on', $notificationData);

            $this->assertContainsEquals($notificationData['id'], $notificationsIds);
            $this->assertContainsEquals($notificationData['user_id'], $notificationsUserIds);
            $this->assertContainsEquals($notificationData['message'], $notificationsViewed);
            $this->assertContainsEquals($notificationData['viewed'], $notificationsViewed);
        }
    }

    #[Test]
    public function itShouldFailGettingNotificationDataNotificationsNotFound(): void
    {
        $page = ValueObjectFactory::createPaginatorPage(1);
        $pageItems = ValueObjectFactory::createPaginatorPageItems(10);
        $userId = ValueObjectFactory::createIdentifier('user id');
        $lang = ValueObjectFactory::createLanguage('en');
        $input = new NotificationGetDataDto($userId, $page, $pageItems, $lang);

        $this->notificationRepository
            ->expects($this->once())
            ->method('getNotificationByUserIdOrFail')
            ->with($userId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
