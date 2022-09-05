<?php

declare(strict_types=1);

namespace Common\Adapter\Validation;

use Common\Adapter\Validation\Validations\ValidationConstraint;
use Common\Domain\Validation\CONSTRAINTS_NAMES;
use Common\Domain\Validation\IValueObjectValidation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private ValidatorInterface $validator;
    private readonly ConstraintsChain $constraintsChain;

    /**
     * @var ValidationConstraint[]
     */
    private array $constraints;
    private mixed $value = null;
    private array $validationCallbacks;

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): ConstraintsChain
    {
        $this->value = $value;

        return $this->constraintsChain;
    }

    public function setConstraint(ValidationConstraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function __construct()
    {
        $this->validator = Validation::createValidator();
        $this->constraintsChain = new ConstraintsChain($this);
        $this->validationCallbacks = $this->getValidationsCallBacks();
    }

    /**
     * @return VALIDATION_ERRORS[]
     */
    public function validate(): array
    {
        $constraints = array_map(
            fn (ValidationConstraint $v) => $v->constraint,
            $this->constraints
        );

        $errors = $this->validator->validate($this->value, $constraints);

        if (0 === count($errors)) {
            return [];
        }

        return $this->getListDomainErrors($errors);
    }

    private function getListDomainErrors(ConstraintViolationList $errors): array
    {
        $errorList = [];

        foreach ($errors->getIterator() as $error) {
            $errorCode = $error->getCode();

            if (null === $errorCode) {
                continue;
            }

            $constraint = $this->findDomainError($errorCode);
            $errorList[] = $constraint->idErrors[$errorCode];
        }

        return $errorList;
    }

    private function findDomainError(string $errorCode): ValidationConstraint
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->hasError($errorCode)) {
                return $constraint;
            }
        }
    }

    /**
     * @return VALIDATION_ERRORS[]
     */
    public function validateValueObject(IValueObjectValidation $valueObject): array
    {
        foreach ($valueObject->getConstraints() as $valueObjectConstraint) {
            $this->validationCallbacks[$valueObjectConstraint->type->value](...$valueObjectConstraint->params);
        }

        return $this
            ->setValue($valueObject->getValue())
            ->validate();
    }

    private function getValidationsCallBacks()
    {
        $constraintCallbacks = [
            CONSTRAINTS_NAMES::NOT_BLANK->value => $this->constraintsChain->notBlank(...),
            CONSTRAINTS_NAMES::NOT_NULL->value => $this->constraintsChain->notNull(...),
            CONSTRAINTS_NAMES::TYPE->value => $this->constraintsChain->type(...),
            CONSTRAINTS_NAMES::EMAIL->value => $this->constraintsChain->email(...),
            CONSTRAINTS_NAMES::EQUAL_TO->value => $this->constraintsChain->equalTo(...),
            CONSTRAINTS_NAMES::NOT_EQUAL_TO->value => $this->constraintsChain->notEqualTo(...),
            CONSTRAINTS_NAMES::IDENTICAL_TO->value => $this->constraintsChain->identicalTo(...),
            CONSTRAINTS_NAMES::NOT_IDENTICAL_TO->value => $this->constraintsChain->notIdenticalTo(...),
            CONSTRAINTS_NAMES::LESS_THAN->value => $this->constraintsChain->lessThan(...),
            CONSTRAINTS_NAMES::LESS_THAN_OR_EQUAL->value => $this->constraintsChain->lessThanOrEqual(...),
            CONSTRAINTS_NAMES::GREATER_THAN->value => $this->constraintsChain->greaterThan(...),
            CONSTRAINTS_NAMES::GREATER_THAN_OR_EQUAL->value => $this->constraintsChain->greaterThanOrEqual(...),
            CONSTRAINTS_NAMES::RANGE->value => $this->constraintsChain->range(...),
            CONSTRAINTS_NAMES::UNIQUE->value => $this->constraintsChain->unique(...),
            CONSTRAINTS_NAMES::POSITIVE->value => $this->constraintsChain->positive(...),
            CONSTRAINTS_NAMES::POSITIVE_OR_ZERO->value => $this->constraintsChain->positiveOrZero(...),
            CONSTRAINTS_NAMES::NEGATIVE->value => $this->constraintsChain->negative(...),
            CONSTRAINTS_NAMES::NEGATIVE_OR_ZERO->value => $this->constraintsChain->negativeOrZero(...),
            CONSTRAINTS_NAMES::STRING_LENGTH->value => $this->constraintsChain->stringLength(...),
            CONSTRAINTS_NAMES::STRING_MIN->value => $this->constraintsChain->stringMin(...),
            CONSTRAINTS_NAMES::STRING_MAX->value => $this->constraintsChain->stringMax(...),
            CONSTRAINTS_NAMES::STRING_RANGE->value => $this->constraintsChain->stringRange(...),
            CONSTRAINTS_NAMES::DATE->value => $this->constraintsChain->date(...),
            CONSTRAINTS_NAMES::DATETIME->value => $this->constraintsChain->dateTime(...),
            CONSTRAINTS_NAMES::TIME->value => $this->constraintsChain->time(...),
            CONSTRAINTS_NAMES::DATETIME->value => $this->constraintsChain->timeZone(...),
            CONSTRAINTS_NAMES::FILE->value => $this->constraintsChain->file(...),
            CONSTRAINTS_NAMES::CHOICE->value => $this->constraintsChain->choice(...),
        ];

        return $constraintCallbacks;
    }
}
