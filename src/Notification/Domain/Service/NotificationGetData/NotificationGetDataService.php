<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationGetData;

use Common\Domain\Ports\Paginator\PaginatorInterface;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationGetData\Dto\NotificationGetDataDto;

class NotificationGetDataService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(NotificationGetDataDto $input): array
    {
        $notifications = $this->notificationRepository->getNotificationByUserIdOrFail($input->userId);
        $notifications->setPagination($input->page->getValue(), $input->pageItems->getValue());

        return $this->getNotificationsData($notifications);
    }

    private function getNotificationsData(PaginatorInterface $notifications): array
    {
        $notificationsData = [];
        foreach ($notifications as $notification) {
            $notificationsData[] = [
                'id' => $notification->getId(),
                'user_id' => $notification->getUserId(),
                'type' => $notification->getType(),
                'viewed' => $notification->getViewed(),
                'created_on' => $notification->getCreatedOn()->format('Y-m-d H:i:s'),
            ];
        }

        return $notificationsData;
    }
}
