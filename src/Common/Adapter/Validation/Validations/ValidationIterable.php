<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\Count;

class ValidationIterable extends ValidationConstraintBase
{
    public function countRange(int|null $min = null, int|null $max = null): ValidationConstraint
    {
        return $this->createConstraint(
            new Count(null, $min, $max),
            [
                Count::TOO_FEW_ERROR => VALIDATION_ERRORS::ITERABLE_TOO_FEW,
                Count::TOO_MANY_ERROR => VALIDATION_ERRORS::ITERABLE_TOO_MANY,
            ]
        );
    }

    public function count(int $value): ValidationConstraint
    {
        return $this->createConstraint(
            new Count($value),
            [
                Count::NOT_EQUAL_COUNT_ERROR => VALIDATION_ERRORS::ITERABLE_NOT_EQUAL,
            ]
        );
    }

    public function countDivisibleBy(int $dividibleBy): ValidationConstraint
    {
        return $this->createConstraint(
            new Count(null, null, null, $dividibleBy),
            [
                Count::NOT_DIVISIBLE_BY_ERROR => VALIDATION_ERRORS::ITERABLE_DIVISIBLE_BY,
            ]
        );
    }
}
