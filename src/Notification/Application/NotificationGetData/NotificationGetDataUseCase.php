<?php

declare(strict_types=1);

namespace Notification\Application\NotificationGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationGetData\Dto\NotificationGetDataInputDto;
use Notification\Application\NotificationGetData\Dto\NotificationGetDataOutputDto;
use Notification\Application\NotificationGetData\Exception\NotificationGetDataNotFoundException;
use Notification\Domain\Service\NotificationGetData\Dto\NotificationGetDataDto;
use Notification\Domain\Service\NotificationGetData\NotificationGetDataService;

class NotificationGetDataUseCase extends ServiceBase
{
    public function __construct(
        private NotificationGetDataService $NotificationGetDataService,
        private ValidationInterface $validator,
    ) {
    }

    public function __invoke(NotificationGetDataInputDto $input): NotificationGetDataOutputDto
    {
        $this->validation($input);

        try {
            $notificationsData = $this->NotificationGetDataService->__invoke(
                $this->createNotificationGetDataDto($input)
            );

            return $this->createNotificationGetDataOutputDto($notificationsData);
        } catch (DBNotFoundException) {
            throw NotificationGetDataNotFoundException::fromMessage('Notifications not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(NotificationGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createNotificationGetDataDto(NotificationGetDataInputDto $input): NotificationGetDataDto
    {
        return new NotificationGetDataDto($input->userSession->getId(), $input->page, $input->pageItems, $input->lang);
    }

    /**
     * @param array<int, array{
     *  id: string|null,
     *  type: string|null,
     *  user_id: string|null,
     *  message: string|null,
     *  data: array<string, string|int|float>,
     *  viewed: bool,
     *  created_on: string
     * }> $notificationsData
     */
    private function createNotificationGetDataOutputDto(array $notificationsData): NotificationGetDataOutputDto
    {
        return new NotificationGetDataOutputDto($notificationsData);
    }
}
