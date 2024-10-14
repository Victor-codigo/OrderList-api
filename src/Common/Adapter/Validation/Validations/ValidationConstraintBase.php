<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraint;

abstract class ValidationConstraintBase
{
    /**
     * @param array<string, VALIDATION_ERRORS> $errors
     */
    protected function createConstraint(Constraint $constraint, array $errors): ValidationConstraint
    {
        return new ValidationConstraint($constraint, $errors);
    }
}
