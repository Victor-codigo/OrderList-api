<?php

declare(strict_types=1);

namespace Common\Domain\Service;

use Common\Domain\Validation\ValidationInterface;

interface ServiceInputDtoInterface
{
    public function validate(ValidationInterface $validator): array;
}
