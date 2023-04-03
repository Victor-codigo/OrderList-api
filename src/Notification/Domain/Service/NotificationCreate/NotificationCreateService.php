<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationCreate;

use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationCreate\Dto\NotificationCreateDto;

class NotificationCreateService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {
    }

    /**
     * @param Identifier[] $usersId
     *
     * @return Notification[]
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(NotificationCreateDto $input): array
    {
        $notification = $this->createNotifications($input->usersId, $input->notificationType);

        $this->notificationRepository->save($notification);

        return $notification;
    }

    /**
     * @param Identifier[] $usersId
     *
     * @return Notification[]
     */
    private function createNotifications(array $usersId, NotificationType $notificationType): array
    {
        $notifications = array_map(
            function (Identifier $userId) use ($notificationType) {
                $notificationId = ValueObjectFactory::createIdentifier($this->notificationRepository->generateId());

                return new Notification($notificationId, $userId, $notificationType);
            },
            $usersId
        );

        return $notifications;
    }
}
