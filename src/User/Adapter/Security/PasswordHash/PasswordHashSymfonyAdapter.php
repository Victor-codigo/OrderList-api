<?php

declare(strict_types=1);

namespace User\Adapter\Security\PasswordHash;

use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;
use User\Domain\Port\PasswordHasher\PasswordHasherInterface;

class PasswordHashSymfonyAdapter implements PasswordHasherInterface
{
    private UserPasswordHasherInterface $passwordHasher;
    private UserSymfonyAdapter $user;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->user = $this->createUserNull();
    }

    public function passwordHash(string $plainPassword): Password
    {
        return ValueObjectFactory::createPassword(
            $this->passwordHasher->hashPassword($this->user, $plainPassword)
        );
    }

    public function passwordIsValid(string $plainPassword): bool
    {
        if ($this->passwordNeedsRehash($this->user->getUser()->getPassword())) {
            $this->user->getUser()->setPassword($this->passwordHash($plainPassword));
        }

        return $this->passwordHasher->isPasswordValid($this->user, $plainPassword);
    }

    public function passwordNeedsRehash(Password $passowrd): bool
    {
        $this->user->getUser()->setPassword($passowrd);

        return $this->passwordHasher->needsRehash($this->user);
    }

    private function createUserNull(): UserSymfonyAdapter
    {
        $user = new User(
            ValueObjectFactory::createEmail(null),
            ValueObjectFactory::createPassword(null),
            ValueObjectFactory::createName(null),
            ValueObjectFactory::createRoles(null),
            ValueObjectFactory::createIdentifier(null),
        );

        return new UserSymfonyAdapter($user);
    }
}
