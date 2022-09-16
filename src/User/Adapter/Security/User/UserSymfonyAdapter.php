<?php

declare(strict_types=1);

namespace User\Adapter\Security\User;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use User\Domain\Model\User;
use User\Domain\Port\User\UserInterface;

class UserSymfonyAdapter implements SymfonyUserInterface, PasswordAuthenticatedUserInterface, UserInterface
{
    private User $user;
    private UserPasswordHasherInterface $passwordHasher;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __construct(User $user, UserPasswordHasherInterface $passwordHasher)
    {
        $this->user = $user;
        $this->passwordHasher = $passwordHasher;
    }

    public function getRoles(): array
    {
        return $this->user
            ->getRoles()
            ->getValue();
    }

    public function eraseCredentials()
    {
        $this->user->setPassword(ValueObjectFactory::createPassword(null));
    }

    public function getUserIdentifier(): string
    {
        return $this->user
            ->getEmail()
            ->getValue();
    }

    public function getPassword(): string
    {
        return $this->user
            ->getPassword()
            ->getValue();
    }

    public function passwordHash(): self
    {
        $this->user->setPassword(ValueObjectFactory::createPassword(
            $this->passwordHasher->hashPassword($this, $this->user->getPassword()->getValue())
        ));

        return $this;
    }

    public function passwordIsValid(string $password): bool
    {
        if ($this->passwordHasher->needsRehash($this)) {
            $this->passwordHash();
        }

        return $this->passwordHasher->isPasswordValid($this, $password);
    }
}
