<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRemember;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Adapter\Http\Controller\UserPasswordRemember\Dto\UserPasswordRememberOutputDto;
use User\Application\UserPasswordRemember\Dto\UserPasswordRememberInputDto;
use User\Application\UserPasswordRemember\Exception\UserPasswordRememberUserNotFoundException;
use User\Domain\Service\UserPasswordRemember\Dto\UserPasswordRememberDto;
use User\Domain\Service\UserPasswordRemember\UserPasswordRememberService as DomainUserPasswordRememberService;

class UserPasswordRememberUseCase extends ServiceBase
{
    public function __construct(
        private DomainUserPasswordRememberService $userPasswordRememberService,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(UserPasswordRememberInputDto $passwordDto): UserPasswordRememberOutputDto
    {
        $this->validation($passwordDto);

        try {
            $this->userPasswordRememberService->__invoke(
                $this->createPasswordRememberDto($passwordDto->email, $passwordDto->passwordRememberUrl)
            );

            return new UserPasswordRememberOutputDto(true);
        } catch (DBNotFoundException) {
            throw UserPasswordRememberUserNotFoundException::fromMessage('Email not found');
        } catch (DomainException) {
            throw DomainInternalErrorException::fromMessage('Email could not be sent, try it later');
        }
    }

    private function validation(UserPasswordRememberInputDto $passwordDto): void
    {
        $errorList = $passwordDto->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Invalid parameters', $errorList);
        }
    }

    private function createPasswordRememberDto(Email $email, Url $passwordRememberUrl): UserPasswordRememberDto
    {
        return new UserPasswordRememberDto($email, $passwordRememberUrl);
    }
}
