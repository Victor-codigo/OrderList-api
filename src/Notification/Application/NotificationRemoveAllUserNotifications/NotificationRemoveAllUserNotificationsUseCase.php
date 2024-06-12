<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemoveAllUserNotifications;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsInputDto;
use Notification\Application\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsOutputDto;
use Notification\Application\NotificationRemoveAllUserNotifications\Exception\NotificationRemoveAllUserNotificationsNotFoundException;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsDto;
use Notification\Domain\Service\NotificationRemoveAllUserNotifications\NotificationRemoveAllUserNotificationsService;
use Notification\Domain\Service\NotificationRemove\NotificationRemoveService;

class NotificationRemoveAllUserNotificationsUseCase extends ServiceBase
{
    public function __construct(
        private NotificationRemoveAllUserNotificationsService $notificationRemoveAllUserNotificationsService,
        private NotificationRemoveService $NotificationRemoveService,
        private ValidationInterface $validator,
        private NotificationRepositoryInterface $notificationRepository
    ) {
    }

    public function __invoke(NotificationRemoveAllUserNotificationsInputDto $input): NotificationRemoveAllUserNotificationsOutputDto
    {
        try {
            $notificationsRemovedId = $this->notificationRemoveAllUserNotificationsService->__invoke(
                $this->createNotificationRemoveAllUserNotificationsDto($input)
            );

            return $this->createNotificationRemoveAllUserNotificationsOutputDto($notificationsRemovedId);
        } catch (DBNotFoundException) {
            throw NotificationRemoveAllUserNotificationsNotFoundException::fromMessage('Notifications not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function createNotificationRemoveAllUserNotificationsDto(NotificationRemoveAllUserNotificationsInputDto $input): NotificationRemoveAllUserNotificationsDto
    {
        return new NotificationRemoveAllUserNotificationsDto($input->userSession->getId());
    }

    private function createNotificationRemoveAllUserNotificationsOutputDto(array $notificationsRemovedId): NotificationRemoveAllUserNotificationsOutputDto
    {
        return new NotificationRemoveAllUserNotificationsOutputDto($notificationsRemovedId);
    }
}
