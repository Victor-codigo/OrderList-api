<?php

declare(strict_types=1);

namespace Common\Adapter\Validation;

use Common\Adapter\Validation\Validations\ValidationConstraint;
use Common\Domain\Validation\Common\CONSTRAINTS_NAMES;
use Common\Domain\Validation\ValidationInterface;
use Common\Domain\Validation\ValueObjectValidationInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private ValidatorInterface $validator;
    private readonly ValidationInterface $validationChain;

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

    public function setValue(mixed $value): ValidationInterface
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
        $errorList = [];
        $constraints = array_map(
            fn (ValidationConstraint $v): Constraint => $v->constraint,
            $this->constraints
        );

        $errors = $this->validator->validate($this->value, $constraints);

        if (count($errors) > 0) {
            $errorList = $this->getListDomainErrors($errors);
        }

        if ($removeConstraints) {
            $this->constraints = [];
        }

        return $errorList;
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
    public function validateValueObject(ValueObjectValidationInterface $valueObject): array
    {
        foreach ($valueObject->getConstraints() as $valueObjectConstraint) {
            $this->validationCallbacks[$valueObjectConstraint->type->value](...$valueObjectConstraint->params);
        }

        $errorList = $this
            ->setValue($valueObject->getValidationValue())
            ->validate(true);

        foreach ($valueObject->getValueObjects() as $index => $childValueObject) {
            $childErrorList = $this->validateValueObject($childValueObject);

            if (empty($childErrorList)) {
                continue;
            }

            $errorList[$this->getNameClass($childValueObject::class, $index + 1)] = $childErrorList;
        }

        return $errorList;
    }

    /**
     * @param array<string, ValueObjectValidationInterface> $valueObjects
     *
     * @return array<string, VALIDATION_ERRORS[]>
     */
    public function validateValueObjectArray(array $valueObjects): array
    {
        $errorList = [];

        foreach ($valueObjects as $name => $valueObject) {
            $errors = $this->validateValueObject($valueObject);

            if (!empty($errors)) {
                $errorList = array_merge($errorList, [$name => $errors]);
            }
        }

        return $errorList;
    }

    private function getNameClass(string $class, int $index): string
    {
        $classInArray = explode('\\', $class);

        return end($classInArray).'-'.$index;
    }

    /**
     * @return array<string, callable>
     */
    private function getValidationsCallBacks(): array
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
            CONSTRAINTS_NAMES::ITERABLE_EQUAL->value => $this->validationChain->count(...),
            CONSTRAINTS_NAMES::ITERABLE_RANGE->value => $this->validationChain->countRange(...),
            CONSTRAINTS_NAMES::ITERABLE_DIVISIBLE_BY->value => $this->validationChain->countDivisibleBy(...),
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
            CONSTRAINTS_NAMES::REGEX->value => $this->validationChain->regEx(...),
            CONSTRAINTS_NAMES::ALPHANUMERIC->value => $this->validationChain->alphanumeric(...),
            CONSTRAINTS_NAMES::ALPHANUMERIC_WITH_WHITESPACE->value => $this->validationChain->alphanumericWithWhiteSpace(...),
            CONSTRAINTS_NAMES::URL->value => $this->validationChain->url(...),
            CONSTRAINTS_NAMES::LANGUAGE->value => $this->validationChain->language(...),
            CONSTRAINTS_NAMES::JSON->value => $this->validationChain->json(...),
            CONSTRAINTS_NAMES::DATE->value => $this->validationChain->date(...),
            CONSTRAINTS_NAMES::DATETIME->value => $this->validationChain->dateTime(...),
            CONSTRAINTS_NAMES::TIME->value => $this->validationChain->time(...),
            CONSTRAINTS_NAMES::TIMEZONE->value => $this->validationChain->timeZone(...),
            CONSTRAINTS_NAMES::FILE->value => $this->validationChain->file(...),
            CONSTRAINTS_NAMES::FILE_IMAGE->value => $this->validationChain->image(...),
            CONSTRAINTS_NAMES::CHOICE->value => $this->validationChain->choice(...),
        ];
    }
}
