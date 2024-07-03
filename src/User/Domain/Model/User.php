<?php

declare(strict_types=1);

namespace User\Domain\Model;

use Common\Domain\Model\ValueObject\Object\Rol;
use DateTime;
use Override;
use Common\Domain\Event\EventRegisterTrait;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserRolesGetterSetterTrait;
use Common\Domain\Service\Image\EntityImageModifyInterface;
use Common\Domain\Validation\User\USER_ROLES;
use User\Domain\Event\UserPreRegistered\UserPreRegisteredEvent;

class User implements EntityImageModifyInterface
{
    use EventRegisterTrait;
    use UserRolesGetterSetterTrait;

    private Identifier $id;
    private Email $email;
    private NameWithSpaces $name;
    private Password $password;
    private Roles $roles;
    private DateTime $createdOn;
    private Profile $profile;

    private ?UserPreRegisteredEvent $userPreRegisteredEventData = null;

    public function setUserPreRegisteredEventData(UserPreRegisteredEvent $data): void
    {
        $this->userPreRegisteredEventData = $data;
    }

    public function getUserPreRegisteredEventData(): ?UserPreRegisteredEvent
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

    public function getName(): NameWithSpaces
    {
        return $this->name;
    }

    public function setName(NameWithSpaces $name): self
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

    #[Override]
    public function getImage(): Path
    {
        return $this->profile->getImage();
    }

    #[Override]
    public function setImage(Path $image): self
    {
        $this->profile->setImage($image);

        return $this;
    }

    public function getCreatedOn(): DateTime
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

    public function __construct(Identifier $id, Email $email, Password $password, NameWithSpaces $name, Roles $roles)
    {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->roles = $roles;
        $this->password = $password;
        $this->createdOn = new DateTime();
        $this->profile = new Profile(
            $this->getId(),
            ValueObjectFactory::createPath(null)
        );
    }

    /**
     * @param USER_ROLES[] $roles
     */
    public static function fromPrimitives(string $id, string $email, string $password, string $name, array $roles): User
    {
        $roles = array_map(
            fn (USER_ROLES $rol): Rol => ValueObjectFactory::createRol($rol),
            $roles
        );

        return new static(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createEmail($email),
            ValueObjectFactory::createPassword($password),
            ValueObjectFactory::createNameWithSpaces($name),
            ValueObjectFactory::createRoles($roles)
        );
    }
}
