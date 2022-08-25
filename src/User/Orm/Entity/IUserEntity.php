<?php

declare(strict_types=1);

namespace User\Orm\Entity;

interface IUserEntity
{
    public function toArray(): array;
}
