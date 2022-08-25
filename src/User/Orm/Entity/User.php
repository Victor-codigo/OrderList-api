<?php

namespace User\Orm\Entity;

use App\Adaptater\IdentificatorAdapter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Dao\UserCreateDao;

final class User implements IUserEntity
{
    private string $id;
    private string $email;
    private string $name;
    private string $password;
    private DateTime $createdOn;
    private Collection $groups;
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

    public function getGroups(): Collection
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

    public function __construct(UserCreateDao $user)
    {
        $this->id = IdentificatorAdapter::createId();
        $this->email = $user->email;
        $this->name = $user->name;
        $this->password = $user->password;
        $this->createdOn = new DateTime();
        $this->groups = new ArrayCollection();
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
            'groups' => $this->groups->toArray(),
            'profile' => $this->profile->toArray(),
        ];
    }
}
