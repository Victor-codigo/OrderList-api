<?php

declare(strict_types=1);

namespace Common\Domain\Security;

use DateTime;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;

class UserShared
{
    use UserRolesGetterSetterTrait;

    public function __construct(
        private Identifier $id,
        private Email $email,
        private NameWithSpaces $name,
        private Roles $roles,
        private Path $image,
        private DateTime $createdOn,
    ) {
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): NameWithSpaces
    {
        return $this->name;
    }

    public function getImage(): Path
    {
        return $this->image;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public static function fromPrimitives(string $id, string $email, string $name, array $roles, ?string $image, DateTime $createdOn): self
    {
        $roles = array_map(
            fn (USER_ROLES $rol) => ValueObjectFactory::createRol($rol),
            $roles
        );

        return new self(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createEmail($email),
            ValueObjectFactory::createNameWithSpaces($name),
            ValueObjectFactory::createRoles($roles),
            ValueObjectFactory::createPath($image),
            $createdOn
        );
    }
}
