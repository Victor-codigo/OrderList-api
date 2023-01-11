<?php

declare(strict_types=1);

namespace User\Application\UserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserRemove\Dto\UserRemoveInputDto;
use User\Application\UserRemove\Exception\UserRemovePermissionsDeniedException;
use User\Application\UserRemove\Exception\UserRemoveUserNotFoundException;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;
use User\Domain\Service\UserRemove\UserRemoveService;

class UserRemoveUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserRemoveService $userRemoveService
    ) {
    }

    /**
     * @throws UserRemovePermissionsDeniedException
     * @throws ValueObjectValidationException
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(UserRemoveInputDto $userRemoveInputDto): void
    {
        $this->validation($userRemoveInputDto);
        $this->hasGrantsOrFail($userRemoveInputDto->userSession, $userRemoveInputDto->userId);

        try {
            $this->userRemoveService->__invoke(
                $this->createUserRemoveDto($userRemoveInputDto->userId)
            );
        } catch (DBNotFoundException) {
            throw UserRemoveUserNotFoundException::formMessage('User not found');
        }
    }

    private function createUserRemoveDto(Identifier $userId): UserRemoveDto
    {
        return new UserRemoveDto($userId);
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(UserRemoveInputDto $userRemoveInputDto): void
    {
        $errorList = $userRemoveInputDto->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Invalid id', $errorList);
        }
    }

    /**
     * @throws UserRemovePermissionsDeniedException
     */
    private function hasGrantsOrFail(User $userSession, Identifier $userId): void
    {
        if ($userSession->getRoles()->has(ValueObjectFactory::createRol(USER_ROLES::ADMIN))) {
            return;
        }

        if ($userSession->getId()->equalTo($userId)) {
            return;
        }

        throw UserRemovePermissionsDeniedException::fromMessage('You have no permissions');
    }
}
