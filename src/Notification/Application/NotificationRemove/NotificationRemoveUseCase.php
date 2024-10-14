<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemove;

use Common\Domain\Config\AppConfig;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationRemove\Dto\NotificationRemoveInputDto;
use Notification\Application\NotificationRemove\Dto\NotificationRemoveOutputDto;
use Notification\Application\NotificationRemove\Exception\NotificationRemoveNotFoundException;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationRemove\Dto\NotificationRemoveDto;
use Notification\Domain\Service\NotificationRemove\NotificationRemoveService;

class NotificationRemoveUseCase extends ServiceBase
{
    private const int PAGINATION_PAGE = 1;
    private const int PAGINATION_PAGE_ITEMS = AppConfig::ENDPOINT_NOTIFICATION_REMOVE_MAX;

    public function __construct(
        private NotificationRemoveService $NotificationRemoveService,
        private ValidationInterface $validator,
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function __invoke(NotificationRemoveInputDto $input): NotificationRemoveOutputDto
    {
        $this->validation($input);

        try {
            $notifications = $this->notificationRepository->getNotificationsByIdOrFail($input->notificationIds);
            $notifications->setPagination(self::PAGINATION_PAGE, self::PAGINATION_PAGE_ITEMS);
            $notificationsValidToDelete = $this->getValidNotificationsToDelete($input->userSession, $notifications);

            $notificationsIdRemoved = $this->NotificationRemoveService->__invoke(
                $this->createNotificationRemoveDto($notificationsValidToDelete)
            );

            return $this->createNotificationRemoveOutputDto($notificationsIdRemoved);
        } catch (DBNotFoundException) {
            throw NotificationRemoveNotFoundException::fromMessage('Notifications not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(NotificationRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param PaginatorInterface<int, Notification> $notificationsToDelete
     *
     * @return Notification[]
     */
    private function getValidNotificationsToDelete(UserShared $userSession, PaginatorInterface $notificationsToDelete): array
    {
        $userSessionId = $userSession->getId();

        $validNotifications = [];
        foreach ($notificationsToDelete as $notification) {
            if ($notification->getUserId()->equalTo($userSessionId)) {
                $validNotifications[] = $notification;
            }
        }

        return $validNotifications;
    }

    /**
     * @param Notification[] $notifications
     */
    private function createNotificationRemoveDto(array $notifications): NotificationRemoveDto
    {
        $notificationsId = array_map(
            fn (Notification $notification): Identifier => $notification->getId(),
            $notifications
        );

        return new NotificationRemoveDto($notificationsId);
    }

    /**
     * @param Notification[] $notifications
     */
    private function createNotificationRemoveOutputDto(array $notifications): NotificationRemoveOutputDto
    {
        $notificationsId = array_map(
            fn (Notification $notification): Identifier => $notification->getId(),
            $notifications
        );

        return new NotificationRemoveOutputDto($notificationsId);
    }
}
