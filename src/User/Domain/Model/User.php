<?php

namespace User\Domain\Model;

use Common\Domain\Event\EventRegisterTrait;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\Event\UserPreRegistered\UserPreRegisteredEvent;

class User
{
    use EventRegisterTrait;

    private Identifier $id;
    private Email $email;
    private Name $name;
    private Password $password;
    private Roles $roles;
    private \DateTime $createdOn;
    private Collection $groups;
    private Profile $profile;

    private UserPreRegisteredEvent $userPreRegisteredEventData;

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

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
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

    public function getRoles(): Roles
    {
        $roles = $this->roles->getValue();
        $rolSearched = new Rol(USER_ROLES::USER);
        $rolNotActive = new Rol(USER_ROLES::NOT_ACTIVE);

        if (!$this->roles->has($rolSearched) && !$this->roles->has($rolNotActive)) {
            $roles[] = $rolSearched;
        }

        return ValueObjectFactory::createRoles($roles);
    }

    public function setRoles(Roles $roles): self
    {
        $this->roles = $roles;

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
        $this->groups = new ArrayCollection([]);
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

    public function setUserPreRegisteredEventData(UserPreRegisteredEvent $data)
    {
        $this->userPreRegisteredEventData = $data;
    }

    public function onCreated(): void
    {
        if (!isset($this->userPreRegisteredEventData)) {
            $this->userPreRegisteredEventData = new UserPreRegisteredEvent(
                $this->id,
                $this->email,
                ValueObjectFactory::createUrl(null)
            );
        }

        $this->eventDispatchRegister($this->userPreRegisteredEventData);
    }
}
