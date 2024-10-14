<?php

declare(strict_types=1);

namespace Notification\Application\NotificationMarkAsViewed;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationCreate\Exception\NotificationCreateUsersValidationException;
use Notification\Application\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedInputDto;
use Notification\Application\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedOutputDto;
use Notification\Application\NotificationMarkAsViewed\Exception\NotificationNotFoundException;
use Notification\Domain\Model\Notification;
use Notification\Domain\Service\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedDto;
use Notification\Domain\Service\NotificationMarkAsViewed\NotificationMarkAsViewedService;

class NotificationMarkAsViewedUseCase extends ServiceBase
{
    public function __construct(
        private NotificationMarkAsViewedService $notificationMarkAsViewedService,
        private ValidationInterface $validator,
    ) {
    }

    public function __invoke(NotificationMarkAsViewedInputDto $input): NotificationMarkAsViewedOutputDto
    {
        $this->validation($input);

        try {
            $notification = $this->notificationMarkAsViewedService->__invoke(
                $this->createNotificationMarkAsViewedDto($input->notificationsId)
            );

            return $this->createNotificationMarkAsViewedOutputDto($notification);
        } catch (DBNotFoundException) {
            throw NotificationNotFoundException::fromMessage('Notifications not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws NotificationCreateUsersValidationException
     * @throws ModuleCommunicationException
     */
    private function validation(NotificationMarkAsViewedInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param Identifier[] $notificationsId
     */
    private function createNotificationMarkAsViewedDto(array $notificationsId): NotificationMarkAsViewedDto
    {
        return new NotificationMarkAsViewedDto($notificationsId);
    }

    /**
     * @param Notification[] $notifications
     */
    private function createNotificationMarkAsViewedOutputDto(array $notifications): NotificationMarkAsViewedOutputDto
    {
        $notificationsId = array_map(
            fn (Notification $notification): Identifier => $notification->getId(),
            $notifications
        );

        return new NotificationMarkAsViewedOutputDto($notificationsId);
    }
}
