<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationMarkAsViewed;

use Common\Domain\Config\AppConfig;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedDto;

class NotificationMarkAsViewedService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {
    }

    /**
     * @return Notification[]
     *
     * @throws DBConnectionException
     * @throws DBUniqueConstraintException
     */
    public function __invoke(NotificationMarkAsViewedDto $input): array
    {
        $notificationsPaginator = $this->notificationRepository->getNotificationsByIdOrFail($input->notificationId);
        $notificationsPaginator->setPagination(1, AppConfig::ENDPOINT_NOTIFICATION_MARK_AS_VIEWED_MAX);
        $notifications = iterator_to_array($notificationsPaginator);

        array_walk(
            $notifications,
            fn (Notification $notification) => $notification->setViewed(true)
        );

        $this->notificationRepository->save($notifications);

        return $notifications;
    }
}
