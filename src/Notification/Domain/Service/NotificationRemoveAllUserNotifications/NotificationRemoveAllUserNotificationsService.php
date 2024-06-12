<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationRemoveAllUserNotifications;

use Common\Domain\Model\ValueObject\String\Identifier;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsDto;

class NotificationRemoveAllUserNotificationsService
{
    private const NOTIFICATION_PAGINATION_ITEMS = 100;

    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    /**
     * @return Identifier[] Notifications removed
     *
     * @throws DBNotFoundException
     */
    public function __invoke(NotificationRemoveAllUserNotificationsDto $input): array
    {
        $userNotificationsPaginator = $this->notificationRepository->getNotificationByUserIdOrFail($input->userId);

        $notificationsId = [];
        foreach ($userNotificationsPaginator->getAllPages(self::NOTIFICATION_PAGINATION_ITEMS) as $notificationsIterator) {
            $notifications = iterator_to_array($notificationsIterator);
            $notificationsId[] = array_map(
                fn (Notification $notification) => $notification->getId(),
                $notifications
            );

            $this->notificationRepository->remove($notifications);
        }

        return array_merge(...$notificationsId);
    }
}
