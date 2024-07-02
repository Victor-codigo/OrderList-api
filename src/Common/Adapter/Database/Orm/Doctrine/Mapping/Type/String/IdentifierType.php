<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\String\Identifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class IdentifierType extends TypeBase
{
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('CHAR(%d)', $column['length']);
    }

    #[\Override]
    public function getClassImplementationName(): string
    {
        return Identifier::class;
    }
}
