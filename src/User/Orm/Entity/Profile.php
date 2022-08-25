<?php

declare(strict_types=1);

namespace User\Orm\Entity;

class Profile implements IUserEntity
{
    protected string $id;
    protected string|null $image = null;

    public function getImage(): string|null
    {
        return $this->image;
    }

    public function setImage($image): self
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
