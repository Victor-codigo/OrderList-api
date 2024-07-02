<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Exception\System\SystemKeyWrongException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationCreate\Dto\NotificationCreateInputDto;
use Notification\Application\NotificationCreate\Dto\NotificationCreateOutputDto;
use Notification\Application\NotificationCreate\Exception\NotificationCreateSystemKeyWrongException;
use Notification\Application\NotificationCreate\Exception\NotificationCreateUsersValidationException;
use Notification\Domain\Model\Notification;
use Notification\Domain\Service\NotificationCreate\Dto\NotificationCreateDto;
use Notification\Domain\Service\NotificationCreate\NotificationCreateService;

class NotificationCreateUseCase extends ServiceBase
{
    public function __construct(
        private NotificationCreateService $notificationCreateService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    public function __invoke(NotificationCreateInputDto $input): NotificationCreateOutputDto
    {
        try {
            $this->validation($input);
            $notification = $this->notificationCreateService->__invoke(
                $this->createNotificationCreateDto($input->usersId, $input->notificationType, $input->notificationData)
            );

            return $this->createNotificationCreateOutputDto($notification);
        } catch (NotificationCreateUsersValidationException|ValueObjectValidationException $e) {
            throw $e;
        } catch (SystemKeyWrongException) {
            throw NotificationCreateSystemKeyWrongException::fromMessage('The system key is wrong');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws NotificationCreateUsersValidationException
     * @throws ModuleCommunicationException
     */
    private function validation(NotificationCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        $this->validateSystemKey($input->systemKey);
        $this->validateUsersId($input->usersId);
    }

    /**
     * @param Identifier[] $usersId
     *
     * @throws NotificationCreateUsersValidationException
     * @throws ModuleCommunicationException
     */
    private function validateUsersId(array $usersId): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::userGet($usersId)
        );

        if (!empty($response->getErrors()) || !$response->hasContent()) {
            throw NotificationCreateUsersValidationException::fromMessage('Wrong users');
        }

        if (count($response->getData()) !== count($usersId)) {
            throw NotificationCreateUsersValidationException::fromMessage('Wrong users');
        }
    }

    private function validateSystemKey(string $systemKey): void
    {
        if ($this->systemKey !== $systemKey) {
            throw SystemKeyWrongException::fromMessage('Wrong system key');
        }
    }

    private function createNotificationCreateDto(array $usersId, NotificationType $notificationType, NotificationData $notificationData): NotificationCreateDto
    {
        return new NotificationCreateDto($usersId, $notificationType, $notificationData);
    }

    private function createNotificationCreateOutputDto(array $notifications): NotificationCreateOutputDto
    {
        $notificationsId = array_map(
            fn (Notification $notification) => $notification->getId(),
            $notifications
        );

        return new NotificationCreateOutputDto($notificationsId);
    }
}
