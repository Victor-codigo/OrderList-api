<?php

declare(strict_types=1);

namespace Common\Domain\Service;

use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

interface ServiceInputDtoInterface
{
    /**
     * @return array<int|string, VALIDATION_ERRORS[]>
     */
    public function validate(ValidationInterface $validator): array;
}
