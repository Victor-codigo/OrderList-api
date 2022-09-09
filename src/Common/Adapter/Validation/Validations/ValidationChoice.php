<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\Choice;

class ValidationChoice extends ValidationConstraintBase
{
    public function choice(array|null $choices, bool|null $multiple, bool|null $strict, int|null $min, int|null $max): ValidationConstraint
    {
        return $this->createConstraint(
            new Choice([], $choices, null, $multiple, $strict, $min, $max),
            [
                Choice::NO_SUCH_CHOICE_ERROR => VALIDATION_ERRORS::CHOICE_NOT_SUCH,
                Choice::TOO_FEW_ERROR => VALIDATION_ERRORS::CHOICE_TOO_FEW,
                Choice::TOO_MANY_ERROR => VALIDATION_ERRORS::CHOICE_TOO_MUCH,
            ]
        );
    }
}
