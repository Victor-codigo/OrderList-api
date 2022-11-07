<?php

declare(strict_types=1);

namespace User\Domain\Service\UserPasswordChange;

use Common\Domain\Exception\PermissionDeniedException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use User\Domain\Model\USER_ROLES;
use User\Domain\Port\PasswordHasher\PasswordHasherInterface;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Port\User\UserInterface;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\Exception\PasswordNewAndRepeatAreNotTheSameException;
use User\Domain\Service\UserPasswordChange\Exception\PasswordOldIsWrongException;

class UserPasswordChangeService
{
    use RefreshDatabaseTrait;

    private UserRepositoryInterface $userRepository;
    private PasswordHasherInterface|UserInterface $userPasswordHasher;

    public function __construct(UserRepositoryInterface $userRepository, PasswordHasherInterface&UserInterface $userPasswordHasher)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function __invoke(UserPasswordChangeDto $passwordDto): void
    {
        $this->getUser($passwordDto);
        $this->validateNewPassword($passwordDto);

        $this->userPasswordHasher->passwordHash($passwordDto->passwordNew->getValue());
        $this->userRepository->save($this->userPasswordHasher->getUser());
    }

    /**
     * @throws PasswordNewAndRepeatAreNotTheSameException
     */
    private function validateNewPassword(UserPasswordChangeDto $passwordDto)
    {
        if (!$passwordDto->passwordNew->equalTo($passwordDto->passwordNewRepeat)) {
            throw PasswordNewAndRepeatAreNotTheSameException::fromMessage('Password new and new repeat are not equal');
        }
    }

    /**
     * @throws PasswordOldIsWrongException
     * @throws DBNotFoundException
     */
    private function getUser(UserPasswordChangeDto $passwordDto): void
    {
        $user = $this->userRepository->findUserByIdNoCacheOrFail($passwordDto->id);
        $this->userPasswordHasher->setUser($user);

        if (!$passwordDto->checkOldPassword) {
            return;
        }

        if (!$this->userPasswordHasher->passwordIsValid($passwordDto->passwordOld->getValue())) {
            throw PasswordOldIsWrongException::fromMessage('User Password is wrong');
        }

        if ($user->getRoles()->has(new Rol(USER_ROLES::NOT_ACTIVE))) {
            throw PermissionDeniedException::fromMessage('Not allowed to change password', [USER_ROLES::NOT_ACTIVE]);
        }
    }
}
