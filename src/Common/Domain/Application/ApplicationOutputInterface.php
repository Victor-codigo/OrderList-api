<?php

declare(strict_types=1);

namespace Common\Domain\Application;

interface ApplicationOutputInterface
{
    public function toArray(): array;
}
