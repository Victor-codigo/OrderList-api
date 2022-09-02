<?php

declare(strict_types=1);

namespace Common\Adapter\Validation;

use Common\Adapter\Validation\Validations\TYPES;
use Common\Adapter\Validation\Validations\ValidationComparison;
use Common\Adapter\Validation\Validations\ValidationConstraint;
use Common\Adapter\Validation\Validations\ValidationDateTime;
use Common\Adapter\Validation\Validations\ValidationFile;
use Common\Adapter\Validation\Validations\ValidationGeneral;
use Common\Adapter\Validation\Validations\ValidationPositiveNegative;
use Common\Adapter\Validation\Validations\ValidationString;
use Common\Domain\Validation\EMAIL_TYPES;
use DateTime;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private ValidatorInterface $validator;
    private mixed $value = null;
    private ConstraintViolationList $errors;

    private ValidationComparison $comparison;
    private ValidationDateTime $datetime;
    private ValidationFile $file;
    private ValidationGeneral $general;
    private ValidationPositiveNegative $positiveNegative;
    private ValidationString $string;

    /**
     * @var ValidationConstraint[]
     */
    private array $constraints;

    public function __construct()
    {
        $this->validator = Validation::createValidator();
        $this->constraints = [];

        $this->comparison = new ValidationComparison();
        $this->datetime = new ValidationDateTime();
        $this->file = new ValidationFile();
        $this->general = new ValidationGeneral();
        $this->positiveNegative = new ValidationPositiveNegative();
        $this->string = new ValidationString();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function validate(): array
    {
        $constraints = array_map(
            fn (ValidationConstraint $v) => $v->constraint,
            $this->constraints
        );

        $this->errors = $this->validator->validate($this->value, $constraints);

        if (0 === count($this->errors)) {
            return [];
        }

        return $this->getListDomainErrors();
    }

    public function notBlank(): self
    {
        $this->constraints[] = $this->general->notBlank();

        return $this;
    }

    public function notNull(): self
    {
        $this->constraints[] = $this->general->notNull();

        return $this;
    }

    public function type(TYPES $type): self
    {
        $this->constraints[] = $this->general->type($type);

        return $this;
    }

    public function email(EMAIL_TYPES $mode): self
    {
        $this->constraints[] = $this->general->email($mode);

        return $this;
    }

    public function stringLength(int $length): self
    {
        $this->constraints[] = $this->string->stringLength($length);

        return $this;
    }

    public function stringMin(int $min): self
    {
        $this->constraints[] = $this->string->stringMin($min);

        return $this;
    }

    public function stringMax(int $max): self
    {
        $this->constraints[] = $this->string->stringMax($max);

        return $this;
    }

    public function stringRange(int $min, int $max): self
    {
        $this->constraints[] = $this->string->stringRange($min, $max);

        return $this;
    }

    public function equalTo(mixed $value): self
    {
        $this->constraints[] = $this->comparison->equalTo($value);

        return $this;
    }

    public function notEqualTo(mixed $value): self
    {
        $this->constraints[] = $this->comparison->notEqualTo($value);

        return $this;
    }

    public function identicalTo(mixed $value): self
    {
        $this->constraints[] = $this->comparison->identicalTo($value);

        return $this;
    }

    public function notIdenticalTo(mixed $value): self
    {
        $this->constraints[] = $this->comparison->notIdenticalTo($value);

        return $this;
    }

    public function lessThan(int|DateTime $value): self
    {
        $this->constraints[] = $this->comparison->lessThan($value);

        return $this;
    }

    public function lessThanOrEqual(int|DateTime $value): self
    {
        $this->constraints[] = $this->comparison->lessThanOrEqual($value);

        return $this;
    }

    public function greaterThan(int|DateTime $value): self
    {
        $this->constraints[] = $this->comparison->greaterThan($value);

        return $this;
    }

    public function greaterThanOrEqual(int|DateTime $value): self
    {
        $this->constraints[] = $this->comparison->greaterThanOrEqual($value);

        return $this;
    }

    public function range(int|DateTime $min, int|DateTime $max): self
    {
        $this->constraints[] = $this->comparison->range($min, $max);

        return $this;
    }

    public function unique(): self
    {
        $this->constraints[] = $this->general->unique();

        return $this;
    }

    public function positive(): self
    {
        $this->constraints[] = $this->positiveNegative->positive();

        return $this;
    }

    public function positiveOrZero(): self
    {
        $this->constraints[] = $this->positiveNegative->positiveOrZero();

        return $this;
    }

    public function negative(): self
    {
        $this->constraints[] = $this->positiveNegative->negative();

        return $this;
    }

    public function negativeOrZero(): self
    {
        $this->constraints[] = $this->positiveNegative->negativeOrZero();

        return $this;
    }

    public function date(): self
    {
        $this->constraints[] = $this->datetime->date();

        return $this;
    }

    public function dateTime(): self
    {
        $this->constraints[] = $this->datetime->dateTime();

        return $this;
    }

    public function time(): self
    {
        $this->constraints[] = $this->datetime->time();

        return $this;
    }

    /**
     * @param DateTimeZone|null $timeZone
     */
    public function timeZone(int|null $timeZone): self
    {
        $this->constraints[] = $this->datetime->timeZone($timeZone);

        return $this;
    }

    public function file(mixed $maxSize, bool|null $binaryFormat, array|string|null $mimeTypes): self
    {
        $this->constraints[] = $this->file->file($maxSize, $binaryFormat, $mimeTypes);

        return $this;
    }

    private function getListDomainErrors(): array
    {
        $errorList = [];

        foreach ($this->errors->getIterator() as $error) {
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
}
