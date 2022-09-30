<?php

declare(strict_types=1);

namespace User\Adapter\Security\User;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use User\Domain\Model\User;
use User\Domain\Port\User\UserInterface;

class UserSymfonyAdapter implements SymfonyUserInterface, PasswordAuthenticatedUserInterface, UserInterface
{
    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getRoles(): array
    {
        $roles = $this->user
            ->getRoles()
            ->getValue();

        return array_map(
            fn (Rol $rol) => $rol->getValue()->value,
            $roles
        );
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
}
