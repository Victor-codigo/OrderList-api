<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationRemove\Dto\NotificationRemoveDto;

class NotificationRemoveService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {
    }

    /**
     * @return Notification[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(NotificationRemoveDto $input): array
    {
        $notifications = $this->notificationRepository->getNotificationsByIdOrFail($input->notificationsId);
        $notifications = iterator_to_array($notifications);

        $this->notificationRepository->remove($notifications);

        return $notifications;
    }
}
