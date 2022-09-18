<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

interface ValueObjectInterface
{
    public function getValue();

    public function getValueObjects(): array;
}
