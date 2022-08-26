<?php

namespace User\Domain\Model;

use DateTime;
use Global\Infrastructure\Framework\IdGenerator;
use Group\Domain\Model\Group;

final class User extends EntityBase implements IEntity
{
    private string $id;
    private string $email;
    private string $name;
    private string $password;
    private DateTime $createdOn;
    private array $groups;
    private Profile $profile;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

     public function setPassword($password): self
     {
         $this->password = $password;

         return $this;
     }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroups(): iterable
    {
        return $this->groups;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function setProfile($profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function __construct(string $email, string $password, string $name)
    {
        $this->id = IdGenerator::createId();
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->createdOn = new DateTime();
        $this->groups = [];
        $this->profile = new Profile($this->getId());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'password' => $this->password,
            'createdOn' => $this->createdOn->format(DateTime::RFC3339),
            'groups' => array_map(fn (Group $i) => $i->toArray(), $this->groups),
            'profile' => $this->profile->toArray(),
        ];
    }
}
