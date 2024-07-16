<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\PermissionDeniedException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\User\USER_ROLES;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeInputDto;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeOutputDto;
use User\Application\UserPasswordChange\Exception\UserPasswordChangeNotificationException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangeNotPermissionsException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangePasswordNewAndRepeatNewAreNotEqualException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangePasswordOldWrongException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangePermissionException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangeUserNotFoundException;
use User\Domain\Model\User;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\Exception\PasswordNewAndRepeatAreNotTheSameException;
use User\Domain\Service\UserPasswordChange\Exception\PasswordOldIsWrongException;
use User\Domain\Service\UserPasswordChange\UserPasswordChangeService as DomainUserPasswordChangeService;

class UserPasswordChangeUseCase extends ServiceBase
{
    public function __construct(
        private DomainUserPasswordChangeService $userPasswordChangeService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    public function __invoke(UserPasswordChangeInputDto $passwordDto): UserPasswordChangeOutputDto
    {
        $this->validation($passwordDto);

        try {
            $this->userPasswordChangeService->__invoke(
                $this->createUserPasswordChangeDto($passwordDto)
            );

            $this->createNotificationPasswordChanged($passwordDto->id, $this->systemKey);

            return $this->createUserPasswordChangeOutputDto(true);
        } catch (DBNotFoundException) {
            throw UserPasswordChangeUserNotFoundException::fromMessage('It could not change password');
        } catch (PasswordOldIsWrongException) {
            throw UserPasswordChangePasswordOldWrongException::fromMessage('Password old is wrong');
        } catch (PermissionDeniedException) {
            throw UserPasswordChangePermissionException::fromMessage('User is not active');
        } catch (PasswordNewAndRepeatAreNotTheSameException) {
            throw UserPasswordChangePasswordNewAndRepeatNewAreNotEqualException::fromMessage('Password new and Repeat new are not equals');
        }
    }

    private function createUserPasswordChangeDto(UserPasswordChangeInputDto $passwordDto): UserPasswordChangeDto
    {
        return new UserPasswordChangeDto(
            $passwordDto->id,
            $passwordDto->passwordOld,
            $passwordDto->passwordNew,
            $passwordDto->passwordNewRepeat,
            true
        );
    }

    private function validation(UserPasswordChangeInputDto $passwordDto): void
    {
        $errorList = $passwordDto->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Wrong password', $errorList);
        }

        if (!$this->userHasPermissions($passwordDto->userSession, $passwordDto->id)) {
            throw UserPasswordChangeNotPermissionsException::fromMessage('You have not permissions');
        }
    }

    private function userHasPermissions(User $userSession, Identifier $userIdToModify): bool
    {
        if ($userSession->getRoles()->has(new Rol(USER_ROLES::ADMIN))) {
            return true;
        }

        if ($userSession->getId()->equalTo($userIdToModify)) {
            return true;
        }

        return false;
    }

    private function createNotificationPasswordChanged(Identifier $userId, string $systemKey): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationUserPasswordChanged([$userId], $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus()) {
            throw UserPasswordChangeNotificationException::fromMessage('An error was ocurred when trying to send the notification: user password changed');
        }
    }

    private function createUserPasswordChangeOutputDto(bool $success): UserPasswordChangeOutputDto
    {
        return new UserPasswordChangeOutputDto($success);
    }
}
