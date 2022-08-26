<?php

namespace User\Infrastructure\Database\Orm\Doctrine\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Global\Infrastructure\Framework\IdGenerator;
use User\Domain\Model\EntityBase as EntityBaseDomain;
use User\Domain\Model\Profile;
use User\Domain\Model\User as UserDomain;
use User\Exception\InvalidArgumentException;
use User\Infrastructure\Database\Orm\Doctrine\Entity\Profile as profileInfrastructure;

final class User extends EntityBase
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

    public function setGroups(array $groups): self
    {
        $this->groups = new ArrayCollection($groups);

        return $this;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = new profileInfrastructure($this->getId());
        $this->profile->setImage($profile->getImage());

        return $this;
    }

    public function __construct(string $email, string $name, string $password)
    {
        $this->id = IdGenerator::createId();
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->createdOn = new DateTime();
        $this->groups = new ArrayCollection();
        $this->profile = new Profile($this->getId());
    }

    public static function createFromDomain(EntityBaseDomain $user): static
    {
        if (!$user instanceof UserDomain) {
            throw InvalidArgumentException::createFromMessage(\sprintf('EntityBase is not an instance of [%s]', UserDomain::class));
        }

        return new self(
            $user->getEmail(),
            $user->getPassword(),
            $user->getName()
        );
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
