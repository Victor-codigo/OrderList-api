<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Entity;

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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
        ];
    }
}
