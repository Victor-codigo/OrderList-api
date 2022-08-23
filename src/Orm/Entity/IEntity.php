<?php

declare(strict_types=1);

namespace App\Orm\Entity;

interface IEntity
{
    public function toArray(): array;
}
