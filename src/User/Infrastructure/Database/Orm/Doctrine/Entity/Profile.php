<?php

declare(strict_types=1);

namespace User\Infrastructure\Database\Orm\Doctrine\Entity;

use User\Domain\Model\EntityBase as EntityBaseDomain;
use User\Domain\Model\Profile as ProfileDomain;
use User\Exception\InvalidArgumentException;

final class Profile extends EntityBase
{
    private string $id;
    private string|null $image = null;

    public function getImage(): string|null
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function createFromDomain(EntityBaseDomain $profile): static
    {
        if (!$profile instanceof ProfileDomain) {
            throw InvalidArgumentException::createFromMessage(sprintf('EntityBase is not an instance of [%s]', ProfileDomain::class));
        }

        return new self($profile->getId());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
        ];
    }
}
