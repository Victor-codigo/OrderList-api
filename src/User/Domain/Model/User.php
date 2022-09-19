<?php

namespace User\Domain\Model;

use Common\Adapter\IdGenerator\IdGenerator;
use Common\Domain\Exception\DtoInvalidPropertyException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Model\ValueObject\array\Roles;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class User
{
    private Identifier $id;
    private Email $email;
    private Name $name;
    private Password $password;
    private Roles $roles;
    private DateTime $createdOn;
    private Collection $groups;
    private Profile $profile;

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

    public function getCreatedOn(): DateTime
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

        if (!in_array(new Rol(USER_ROLES::USER), $roles)) {
            $roles[] = new Rol(USER_ROLES::USER);
        }

        return ValueObjectFactory::createRoles($roles);
    }

    public function setRoles(Roles $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function __construct(Email $email, Password $password, Name $name, Roles $roles)
    {
        $this->id = ValueObjectFactory::createIdentifier(IdGenerator::createId());
        $this->email = $email;
        $this->name = $name;
        $this->roles = $roles;
        $this->password = $password;
        $this->createdOn = new DateTime();
        $this->groups = new ArrayCollection([]);
        $this->profile = new Profile($this->getId());
    }

    /**
     * @throws DtoInvalidPropertyException
     */
    public static function createFromDto(object $dto): self
    {
        if (!isset($dto->email) || !isset($dto->password) || !isset($dto->name) || !isset($dto->roles)) {
            throw new DtoInvalidPropertyException();
        }

        return new self($dto->email, $dto->password, $dto->name, $dto->roles);
    }
}
