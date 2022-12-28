<?php

declare(strict_types=1);

namespace User\Application\UserEmailChange;

use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserEmailChange\Dto\UserEmailChangeInputDto;
use User\Application\UserEmailChange\Exception\UserEmailChangePasswordWrongException;
use User\Domain\Service\UserEmailChange\Dto\UserEmailChangeInputDto as DomainUserEmailChangeInputDto;
use User\Domain\Service\UserEmailChange\Exception\UserEmailChangePasswordWrongException as DomainUserEmailChangePasswordWrongException;
use User\Domain\Service\UserEmailChange\UserEmailChangeService as DomainUserEmailChangeService;

class UserEmailChangeService extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private DomainUserEmailChangeService $userEmailChangeService
    ) {
    }

    public function __invoke(UserEmailChangeInputDto $userEmailChangeDto)
    {
        $this->validation($userEmailChangeDto);

        try {
            $this->userEmailChangeService->__invoke(
                $this->createUserEmailChangeDto($userEmailChangeDto)
            );
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

    private function createUserEmailChangeDto(UserEmailChangeInputDto $userEmailChangeDto): DomainUserEmailChangeInputDto
    {
        return new DomainUserEmailChangeInputDto(
            $userEmailChangeDto->userEmail,
            $userEmailChangeDto->email,
            $userEmailChangeDto->password
        );
    }
}
