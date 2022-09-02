<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\Negative;
use Symfony\Component\Validator\Constraints\NegativeOrZero;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ValidationPositiveNegative extends ValidationConstraintBase
{
    public function positive(): ValidationConstraint
    {
        return $this->createConstraint(
            new Positive(),
            [Positive::TOO_LOW_ERROR => VALIDATION_ERRORS::POSITIVE]
        );
    }

    public function positiveOrZero(): ValidationConstraint
    {
        return $this->createConstraint(
            new PositiveOrZero(),
            [PositiveOrZero::TOO_LOW_ERROR => VALIDATION_ERRORS::POSITIVE_OR_ZERO]
        );
    }

    public function negative(): ValidationConstraint
    {
        return $this->createConstraint(
            new Negative(),
            [Negative::TOO_HIGH_ERROR => VALIDATION_ERRORS::NEGATIVE]
        );
    }

    public function negativeOrZero(): ValidationConstraint
    {
        return $this->createConstraint(
            new NegativeOrZero(),
            [NegativeOrZero::TOO_HIGH_ERROR => VALIDATION_ERRORS::NEGATIVE_OR_ZERO]
        );
    }
}
