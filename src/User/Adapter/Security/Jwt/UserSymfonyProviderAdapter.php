<?php

declare(strict_types=1);

namespace User\Adapter\Security\Jwt;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Port\User\UserInterface as UserUserInterface;

class UserSymfonyProviderAdapter implements UserProviderInterface, PasswordUpgraderInterface
{
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepositoryInterface $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @throws UnsupportedUserException if the user is not supported
     * @throws UserNotFoundException    if the user is not found
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserSymfonyAdapter) {
            throw new UnsupportedUserException(sprintf('It is not an instance of %s', UserSymfonyAdapter::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return UserSymfonyAdapter::class === $class;
    }

    /**
     * @throws UserNotFoundException
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            if ($this->userRepository->isValidUuid($identifier)) {
                $user = $this->loadUserFromId($identifier);
            } else {
                $user = $this->loadUserFromEmail($identifier);
            }

            if (!$this->isValidUser($user)) {
                throw new DBNotFoundException();
            }

            return $this->createUserSymfonyAdapter($user);
        } catch (DBNotFoundException) {
            throw new UserNotFoundException('email or identifier: ', $identifier);
        }
    }

    private function createUserSymfonyAdapter(User $user): UserSymfonyAdapter
    {
        return new UserSymfonyAdapter($this->passwordHasher, $user);
    }

    /**
     * @throws DBNotFoundException
     */
    private function loadUserFromId(string $identifier): User
    {
        return $this->userRepository->findUserByIdOrFail(
            ValueObjectFactory::createIdentifier($identifier)
        );
    }

    /**
     * @throws DBNotFoundException
     */
    private function loadUserFromEmail(string $email): User
    {
        return $this->userRepository->findUserByEmailOrFail(
            ValueObjectFactory::createEmail($email)
        );
    }

    private function isValidUser(User $user): bool
    {
        return !$user->getRoles()->has(new Rol(USER_ROLES::NOT_ACTIVE));
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof UserUserInterface) {
            return;
        }

        $userEntity = $user->getUser();
        $userEntity->setPassword(ValueObjectFactory::createPassword($newHashedPassword));
        $this->userRepository->save($userEntity);
    }
}
