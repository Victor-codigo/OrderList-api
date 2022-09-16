<?php

declare(strict_types=1);

namespace Common\Adapter\Validation;

use Common\Adapter\Validation\Validations\ValidationConstraint;
use Common\Domain\Validation\CONSTRAINTS_NAMES;
use Common\Domain\Validation\IValidation;
use Common\Domain\Validation\IValueObjectValidation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private ValidatorInterface $validator;
    private readonly IValidation $validationChain;

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

    public function setValue(mixed $value): IValidation
    {
        $this->value = $value;

        return $this->validationChain;
    }

    public function setConstraint(ValidationConstraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function __construct(ValidationChain $validationChain)
    {
        $this->validator = Validation::createValidator();
        $this->validationChain = $validationChain;
        $this->validationCallbacks = $this->getValidationsCallBacks();
    }

    /**
     * @return VALIDATION_ERRORS[]
     */
    public function validate(bool $removeConstraints = true): array
    {
        $errors = [];
        $constraints = array_map(
            fn (ValidationConstraint $v) => $v->constraint,
            $this->constraints
        );

        $constraintErrors = $this->validator->validate($this->value, $constraints);

        if (0 !== count($constraintErrors)) {
            $errors = $this->getListDomainErrors($constraintErrors);
        }

        if ($removeConstraints) {
            $this->constraints = [];
        }

        return $errors;
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

            if (null !== $constraint) {
                $errorList[] = $constraint->idErrors[$errorCode];
            }
        }

        return $errorList;
    }

    private function findDomainError(string $errorCode): ValidationConstraint|null
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->hasError($errorCode)) {
                return $constraint;
            }
        }

        return null;
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
            ->validate(true);
    }

    /**
     * @param IValueObjectValidation $valueObjects
     */
    public function validateValueObjectArray(array $valueObjects): array
    {
        $errors = [];

        foreach ($valueObjects as $valueObject) {
            $errors = array_merge($errors, $this->validateValueObject($valueObject));
        }

        return $errors;
    }

    private function getValidationsCallBacks()
    {
        return [
            CONSTRAINTS_NAMES::NOT_BLANK->value => $this->validationChain->notBlank(...),
            CONSTRAINTS_NAMES::NOT_NULL->value => $this->validationChain->notNull(...),
            CONSTRAINTS_NAMES::TYPE->value => $this->validationChain->type(...),
            CONSTRAINTS_NAMES::EMAIL->value => $this->validationChain->email(...),
            CONSTRAINTS_NAMES::EQUAL_TO->value => $this->validationChain->equalTo(...),
            CONSTRAINTS_NAMES::NOT_EQUAL_TO->value => $this->validationChain->notEqualTo(...),
            CONSTRAINTS_NAMES::IDENTICAL_TO->value => $this->validationChain->identicalTo(...),
            CONSTRAINTS_NAMES::NOT_IDENTICAL_TO->value => $this->validationChain->notIdenticalTo(...),
            CONSTRAINTS_NAMES::LESS_THAN->value => $this->validationChain->lessThan(...),
            CONSTRAINTS_NAMES::LESS_THAN_OR_EQUAL->value => $this->validationChain->lessThanOrEqual(...),
            CONSTRAINTS_NAMES::GREATER_THAN->value => $this->validationChain->greaterThan(...),
            CONSTRAINTS_NAMES::GREATER_THAN_OR_EQUAL->value => $this->validationChain->greaterThanOrEqual(...),
            CONSTRAINTS_NAMES::RANGE->value => $this->validationChain->range(...),
            CONSTRAINTS_NAMES::UNIQUE->value => $this->validationChain->unique(...),
            CONSTRAINTS_NAMES::POSITIVE->value => $this->validationChain->positive(...),
            CONSTRAINTS_NAMES::POSITIVE_OR_ZERO->value => $this->validationChain->positiveOrZero(...),
            CONSTRAINTS_NAMES::NEGATIVE->value => $this->validationChain->negative(...),
            CONSTRAINTS_NAMES::NEGATIVE_OR_ZERO->value => $this->validationChain->negativeOrZero(...),
            CONSTRAINTS_NAMES::STRING_LENGTH->value => $this->validationChain->stringLength(...),
            CONSTRAINTS_NAMES::STRING_MIN->value => $this->validationChain->stringMin(...),
            CONSTRAINTS_NAMES::STRING_MAX->value => $this->validationChain->stringMax(...),
            CONSTRAINTS_NAMES::STRING_RANGE->value => $this->validationChain->stringRange(...),
            CONSTRAINTS_NAMES::UUID->value => $this->validationChain->uuId(...),
            CONSTRAINTS_NAMES::DATE->value => $this->validationChain->date(...),
            CONSTRAINTS_NAMES::DATETIME->value => $this->validationChain->dateTime(...),
            CONSTRAINTS_NAMES::TIME->value => $this->validationChain->time(...),
            CONSTRAINTS_NAMES::DATETIME->value => $this->validationChain->timeZone(...),
            CONSTRAINTS_NAMES::FILE->value => $this->validationChain->file(...),
            CONSTRAINTS_NAMES::CHOICE->value => $this->validationChain->choice(...),
        ];
    }
}
