<?php

declare(strict_types=1);

namespace User\Application\UserEmailChange;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserEmailChange\Dto\UserEmailChangeInputDto;
use User\Application\UserEmailChange\Exception\UserEmailChangeCreateNotificationException;
use User\Application\UserEmailChange\Exception\UserEmailChangePasswordWrongException;
use User\Domain\Service\UserEmailChange\Dto\UserEmailChangeInputDto as DomainUserEmailChangeInputDto;
use User\Domain\Service\UserEmailChange\Exception\UserEmailChangePasswordWrongException as DomainUserEmailChangePasswordWrongException;
use User\Domain\Service\UserEmailChange\UserEmailChangeService as DomainUserEmailChangeService;

class UserEmailChangeUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private DomainUserEmailChangeService $userEmailChangeService,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    public function __invoke(UserEmailChangeInputDto $userEmailChangeDto): void
    {
        $this->validation($userEmailChangeDto);

        try {
            $this->userEmailChangeService->__invoke(
                $this->createUserEmailChangeDto($userEmailChangeDto)
            );

            $this->createUserEmailChangedNotification($userEmailChangeDto->userId, $this->systemKey);
        } catch (DomainUserEmailChangePasswordWrongException) {
            throw UserEmailChangePasswordWrongException::fromMessage('Password is wrong');
        }
    }

    private function validation(UserEmailChangeInputDto $userEmailChangeDto): void
    {
        $errorList = $userEmailChangeDto->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createUserEmailChangedNotification(Identifier $userId, string $systemKey): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationUserEmailChanged($userId, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus()) {
            throw UserEmailChangeCreateNotificationException::fromMessage('An error was ocurred when trying to send the notification: user email changed');
        }
    }

    private function createUserEmailChangeDto(UserEmailChangeInputDto $userEmailChangeDto): DomainUserEmailChangeInputDto
    {
        return new DomainUserEmailChangeInputDto(
            $userEmailChangeDto->userEmail,
            $userEmailChangeDto->email,
            $userEmailChangeDto->password
        );
    }
}
