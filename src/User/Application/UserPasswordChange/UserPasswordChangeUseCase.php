<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\PermissionDeniedException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeInputDto;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeOutputDto;
use User\Application\UserPasswordChange\Exception\UserPasswordChangePasswordNewAndRepeatNewAreNotEqualException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangePasswordOldWrongException;
use User\Application\UserPasswordChange\Exception\UserpasswordChangePermissionException;
use User\Application\UserPasswordChange\Exception\UserPasswordChangeUserNotFoundException;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\Exception\PasswordNewAndRepeatAreNotTheSameException;
use User\Domain\Service\UserPasswordChange\Exception\PasswordOldIsWrongException;
use User\Domain\Service\UserPasswordChange\UserPasswordChangeService as DomainUserPasswordChangeService;

class UserPasswordChangeUseCase extends ServiceBase
{
    private DomainUserPasswordChangeService $userPasswordChangeService;
    private ValidationInterface $validator;

    public function __construct(DomainUserPasswordChangeService $userPasswordChangeService, ValidationInterface $validator)
    {
        $this->userPasswordChangeService = $userPasswordChangeService;
        $this->validator = $validator;
    }

    public function __invoke(UserPasswordChangeInputDto $passwordDto): UserPasswordChangeOutputDto
    {
        $this->validation($passwordDto);

        try {
            $this->userPasswordChangeService->__invoke(
                $this->createUserPasswordChangeDto($passwordDto)
            );

            return $this->createUserPasswordChangeOutputDto(true);
        } catch (DBNotFoundException) {
            throw UserPasswordChangeUserNotFoundException::fromMessage('It could not change password');
        } catch (PasswordOldIsWrongException) {
            throw UserPasswordChangePasswordOldWrongException::fromMessage('Pasword old is wrong');
        } catch (PermissionDeniedException) {
            throw UserpasswordChangePermissionException::fromMessage('User is not active');
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
    }

    private function createUserPasswordChangeOutputDto(bool $sucess): UserPasswordChangeOutputDto
    {
        return new UserPasswordChangeOutputDto($sucess);
    }
}
