<?php

declare(strict_types=1);

namespace User\Domain\Service\UserFirstLogin;

use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserCreateGroup\Dto\UserCreateGroupDto;
use User\Domain\Service\UserCreateGroup\Exception\UserCreateGroupUserException;
use User\Domain\Service\UserCreateGroup\UserCreateGroupService;
use User\Domain\Service\UserFirstLogin\Dto\UserFirstLoginDto;
use User\Domain\Service\UserFirstLogin\Exception\UserFirstLoginCreateGroupException as ExceptionUserFirstLoginCreateGroupException;

class UserFirstLoginService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserCreateGroupService $userCreateGroupService,
    ) {
    }

    /**
     * @throws UserFirsLoginCreateGroupException
     */
    public function __invoke(UserFirstLoginDto $input): void
    {
        if (!$this->isFirstLogin($input->user)) {
            return;
        }

        try {
            $this->userCreateGroupService->__invoke(
                $this->createUserCreateGroupDto($input->user->getName())
            );
        } catch (UserCreateGroupUserException) {
            throw ExceptionUserFirstLoginCreateGroupException::formMessage('Could not create the user\'s group');
        }

        $this->changeRoleToUser($input->user);
    }

    private function isFirstLogin(User $user): bool
    {
        if ($user->getRoles()->has(ValueObjectFactory::createRol(USER_ROLES::USER_FIRST_LOGIN))) {
            return true;
        }

        return false;
    }

    private function changeRoleToUser(User $user): void
    {
        $user->setRoles(ValueObjectFactory::createRoles([
            ValueObjectFactory::createRol(USER_ROLES::USER),
        ]));

        $this->userRepository->save($user);
    }

    private function createUserCreateGroupDto(Name $userName): UserCreateGroupDto
    {
        return new UserCreateGroupDto($userName);
    }
}
