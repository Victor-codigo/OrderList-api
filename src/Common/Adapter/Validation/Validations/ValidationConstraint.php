<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraint;

class ValidationConstraint
{
    public readonly Constraint $constraint;

    /**
     * @var array<string, VALIDATION_ERRORS> key = id validator error
     *                                       value = id domain error
     */
    public readonly array $idErrors;

    /**
     * @param array<string, VALIDATION_ERRORS> $idErrors key = id validator error
     *                                                   value = id domain error
     */
    public function __construct(Constraint $constraint, array $idErrors)
    {
        $this->constraint = $constraint;
        $this->idErrors = $idErrors;
    }

    public function hasError(string $idError): bool
    {
        return array_key_exists($idError, $this->idErrors);
    }
}
