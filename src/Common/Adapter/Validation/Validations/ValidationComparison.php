<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use DateTime;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotIdenticalTo;
use Symfony\Component\Validator\Constraints\Range;

class ValidationComparison extends ValidationConstraintBase
{
    public function equalTo(mixed $value): ValidationConstraint
    {
        return $this->createConstraint(
            new EqualTo($value),
            [EqualTo::NOT_EQUAL_ERROR => VALIDATION_ERRORS::EQUAL_TO]
        );
    }

    public function notEqualTo(mixed $value): ValidationConstraint
    {
        return $this->createConstraint(
            new NotEqualTo($value),
            [NotEqualTo::IS_EQUAL_ERROR => VALIDATION_ERRORS::NOT_EQUAL_TO],
        );
    }

    public function identicalTo(mixed $value): ValidationConstraint
    {
        return $this->createConstraint(
            new IdenticalTo($value),
            [IdenticalTo::NOT_IDENTICAL_ERROR => VALIDATION_ERRORS::IDENTICAL_TO],
        );
    }

    public function notIdenticalTo(mixed $value): ValidationConstraint
    {
        return $this->createConstraint(
            new NotIdenticalTo($value),
            [NotIdenticalTo::IS_IDENTICAL_ERROR => VALIDATION_ERRORS::NOT_IDENTICAL_TO],
        );
    }

    public function lessThan(int|DateTime $value): ValidationConstraint
    {
        return $this->createConstraint(
            new LessThan($value),
            [LessThan::TOO_HIGH_ERROR => VALIDATION_ERRORS::LESS_THAN]
        );
    }

    public function lessThanOrEqual(int|DateTime $value): ValidationConstraint
    {
        return $this->createConstraint(
            new LessThanOrEqual($value),
            [LessThanOrEqual::TOO_HIGH_ERROR => VALIDATION_ERRORS::LESS_THAN_OR_EQUAL],
        );
    }

    public function greaterThan(int|DateTime $value): ValidationConstraint
    {
        return $this->createConstraint(
            new GreaterThan($value),
            [GreaterThan::TOO_LOW_ERROR => VALIDATION_ERRORS::GREATER_THAN],
        );

        return $this;
    }

    public function greaterThanOrEqual(int|DateTime $value): ValidationConstraint
    {
        return $this->createConstraint(
            new GreaterThanOrEqual($value),
            [GreaterThanOrEqual::TOO_LOW_ERROR => VALIDATION_ERRORS::GREATER_THAN_OR_EQUAL],
        );
    }

    public function range(int|DateTime $min, int|DateTime $max): ValidationConstraint
    {
        return $this->createConstraint(
            new Range(null, null, null, null, null, null, $min, null, $max),
            [
                Range::TOO_LOW_ERROR => VALIDATION_ERRORS::RANGE_TOO_LOW,
                Range::TOO_HIGH_ERROR => VALIDATION_ERRORS::RANGE_TOO_HIGH,
                Range::NOT_IN_RANGE_ERROR => VALIDATION_ERRORS::RANGE_NOT_IN_RANGE,
                Range::INVALID_CHARACTERS_ERROR => VALIDATION_ERRORS::RANGE_INVALID_CHARACTERS,
            ],
        );
    }
}
