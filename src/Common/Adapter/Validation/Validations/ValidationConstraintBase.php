<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Symfony\Component\Validator\Constraint;

abstract class ValidationConstraintBase
{
    /**
     * @param string[] $errors key = id validator error
     *                         value = id domain error
     */
    protected function createConstraint(Constraint $constraint, array $errors): ValidationConstraint
    {
        return new ValidationConstraint($constraint, $errors);
    }
}
