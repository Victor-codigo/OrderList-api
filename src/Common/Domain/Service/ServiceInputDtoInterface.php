<?php

declare(strict_types=1);

namespace Common\Domain\Service;

use Common\Domain\Validation\IValidation;

interface ServiceInputDtoInterface
{
    public function validate(IValidation $validator): array;
}
