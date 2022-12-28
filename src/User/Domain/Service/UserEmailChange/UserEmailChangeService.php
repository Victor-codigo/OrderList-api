<?php

declare(strict_types=1);

namespace User\Domain\Service\UserEmailChange;

use User\Domain\Model\User;
use User\Domain\Port\PasswordHasher\PasswordHasherInterface;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Port\User\UserInterface;
use User\Domain\Service\UserEmailChange\Dto\UserEmailChangeInputDto;
use User\Domain\Service\UserEmailChange\Exception\UserEmailChangePasswordWrongException;

class UserEmailChangeService
{
    public function __construct(
        private UserInterface&PasswordHasherInterface $userPasswordHasher,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @throws UserEmailChangePasswordWrongException
     */
    public function __invoke(UserEmailChangeInputDto $userEmailChangeDto): void
    {
        $user = $this->getUser($userEmailChangeDto);
        $user->setEmail($userEmailChangeDto->email);

        $this->userRepository->save($user);
    }

    /**
     * @throws UserEmailChangePasswordWrongException
     */
    private function getUser(UserEmailChangeInputDto $userEmailChangeDto): User
    {
        $user = $this->userRepository->findUserByEmailOrFail($userEmailChangeDto->userEmail);
        $this->userPasswordHasher->setUser($user);

        if (!$this->userPasswordHasher->passwordIsValid($userEmailChangeDto->password->getValue())) {
            throw UserEmailChangePasswordWrongException::fromMessage('User Password is wrong');
        }

        return $user;
    }
}
