<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\String\Language;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class LanguageType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('VARCHAR(%d)', $column['length']);
    }

    public function getClassImplementationName(): string
    {
        return Language::class;
    }
}
