<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject\Integer;

class Age extends IntegerValueObject
{
    public function __construct(int $value)
    {
        parent::__construct($value);
    }
}
