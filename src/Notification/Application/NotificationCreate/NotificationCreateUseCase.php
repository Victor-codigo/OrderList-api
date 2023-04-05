<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate;

use App\Common\Domain\Exception\System\SystemKeyWrongException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Domain\Exception\DomainInternalErrorException;
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
        private NotificationCreateService $NotificationCreateService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    public function __invoke(NotificationCreateInputDto $input): NotificationCreateOutputDto
    {
        try {
            $this->validation($input);
            $notification = $this->NotificationCreateService->__invoke(
                $this->createNotificationCreateDto($input->usersId, $input->notificationType)
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
        $usersIdPlain = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $usersId
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::userGet($usersIdPlain)
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
            throw SystemKeyWrongException::formMessage('Wrong system key');
        }
    }

    /**
     * @param Identifier[] $userId
     */
    private function createNotificationCreateDto(array $usersId, NotificationType $notificationType): NotificationCreateDto
    {
        return new NotificationCreateDto($usersId, $notificationType);
    }

    /**
     * @param Identifier[] $notificationId
     */
    private function createNotificationCreateOutputDto(array $notifications): NotificationCreateOutputDto
    {
        $notificationsId = array_map(
            fn (Notification $notification) => $notification->getId(),
            $notifications
        );

        return new NotificationCreateOutputDto($notificationsId);
    }
}
