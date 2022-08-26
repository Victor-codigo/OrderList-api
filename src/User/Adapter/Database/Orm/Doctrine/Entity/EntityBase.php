<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Entity;

use User\Domain\Model\EntityBase as EntityBaseDomain;

abstract class EntityBase
{
    abstract public static function createFromDomain(EntityBaseDomain $entity): static;
}
