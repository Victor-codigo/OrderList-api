<?php

declare(strict_types=1);

namespace Common\Adapter\Validation;

use App\Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private ValidatorInterface $validator;
    private array $constraints;

    public function __construct()
    {
        $this->validator = Validation::createValidator();
        $this->constraints = [];
    }

    public function validate(mixed $value): array
    {
        $errors = $this->validator->validate($value, $this->constraints);

        if (0 === count($errors)) {
            return [];
        }

        return $this->getListErrors($errors);
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function notBlank(): self
    {
        $this->constraints[] = new NotBlank();

        return $this;
    }

    private function getListErrors(ConstraintViolationListInterface $errors): array
    {
        $errors = [];

        foreach ($errors as $error) {
            $errors[] = match ($error->getConstraint()->getErrorName()) {
                NotBlank::IS_BLANK_ERROR => VALIDATION_ERRORS::NOT_BLANK
            };
        }

        return $errors;
    }
}
