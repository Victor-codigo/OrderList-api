<?php

declare(strict_types=1);

namespace User\Domain\Model;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;

class Profile
{
    protected Identifier $id;
    protected Path $image;

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getImage(): Path
    {
        return $this->image;
    }

    public function setImage(Path $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function __construct(Identifier $id, Path $image)
    {
        $this->id = $id;
        $this->image = $image;
    }
}
