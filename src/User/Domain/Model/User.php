<?php

namespace User\Domain\Model;

use Common\Domain\Event\EventRegisterTrait;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserRolesGetterSetterTrait;
use Common\Domain\Validation\User\USER_ROLES;
use User\Domain\Event\UserPreRegistered\UserPreRegisteredEvent;

class User
{
    use EventRegisterTrait;
    use UserRolesGetterSetterTrait;

    private Identifier $id;
    private Email $email;
    private Name $name;
    private Password $password;
    private Roles $roles;
    private \DateTime $createdOn;
    private Profile $profile;

    private UserPreRegisteredEvent|null $userPreRegisteredEventData = null;

    public function setUserPreRegisteredEventData(UserPreRegisteredEvent $data)
    {
        $this->userPreRegisteredEventData = $data;
    }

    public function getUserPreRegisteredEventData(): UserPreRegisteredEvent|null
    {
        return $this->userPreRegisteredEventData;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function setPassword(Password $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getImage(): Path
    {
        return $this->profile->getImage();
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function __construct(Identifier $id, Email $email, Password $password, Name $name, Roles $roles)
    {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->roles = $roles;
        $this->password = $password;
        $this->createdOn = new \DateTime();
        $this->profile = new Profile($this->getId());
    }

    /**
     * @param USER_ROLES[] $roles
     */
    public static function fromPrimitives(string $id, string $email, string $password, string $name, array $roles): User
    {
        $roles = array_map(
            fn (USER_ROLES $rol) => ValueObjectFactory::createRol($rol),
            $roles
        );

        return new static(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createEmail($email),
            ValueObjectFactory::createPassword($password),
            ValueObjectFactory::createName($name),
            ValueObjectFactory::createRoles($roles)
        );
    }
}
