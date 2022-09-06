<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Symfony\Component\Validator\Constraint;

class ValidationConstraint
{
    public readonly Constraint $constraint;

    /**
     * @var string[] key = id validator error
     *               value = id domain error
     */
    public readonly array $idErrors;

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
