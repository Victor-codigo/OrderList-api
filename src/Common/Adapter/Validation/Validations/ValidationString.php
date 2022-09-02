<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\Length;

class ValidationString extends ValidationConstraintBase
{
    public function stringLength(int $length): ValidationConstraint
    {
        return $this->createStringConstraint($length, null, null);
    }

    public function stringMin(int $min): ValidationConstraint
    {
        return $this->createStringConstraint(null, $min, null);
    }

    public function stringMax(int $max): ValidationConstraint
    {
        return $this->createStringConstraint(null, null, $max);
    }

    public function stringRange(int $min, int $max): ValidationConstraint
    {
        return $this->createStringConstraint(null, $min, $max);
    }

    private function createStringConstraint(int|null $exactly, int|null $min, int|null $max): ValidationConstraint
    {
        return $this->createConstraint(
            new Length($exactly, $min, $max),
            [
                Length::INVALID_CHARACTERS_ERROR => VALIDATION_ERRORS::STRING_INVALID_CHARACTERS,
                Length::NOT_EQUAL_LENGTH_ERROR => VALIDATION_ERRORS::STRING_NOT_EQUAL_LENGTH,
                Length::TOO_LONG_ERROR => VALIDATION_ERRORS::STRING_TOO_LONG,
                Length::TOO_SHORT_ERROR => VALIDATION_ERRORS::STRING_TOO_SHORT,
            ]
        );
    }
}
