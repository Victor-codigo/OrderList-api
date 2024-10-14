<?php

declare(strict_types=1);

namespace Common\Domain\Application;

interface ApplicationOutputInterface
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
