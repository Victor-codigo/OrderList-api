<?php

declare(strict_types=1);

namespace Common\Adapter\Validation;

use Common\Adapter\Validation\Validations\ValidationChoice;
use Common\Adapter\Validation\Validations\ValidationComparison;
use Common\Adapter\Validation\Validations\ValidationConstraint;
use Common\Adapter\Validation\Validations\ValidationDateTime;
use Common\Adapter\Validation\Validations\ValidationFactory;
use Common\Adapter\Validation\Validations\ValidationFile;
use Common\Adapter\Validation\Validations\ValidationGeneral;
use Common\Adapter\Validation\Validations\ValidationPositiveNegative;
use Common\Adapter\Validation\Validations\ValidationString;
use Common\Domain\Validation\EMAIL_TYPES;
use Common\Domain\Validation\TYPES;
use Common\Domain\Validation\ValidationInterface;
use Common\Domain\Validation\ValueObjectValidationInterface;

class ValidationChain implements ValidationInterface
{
    private ValidationComparison $comparison;
    private ValidationDateTime $datetime;
    private ValidationFile $file;
    private ValidationGeneral $general;
    private ValidationPositiveNegative $positiveNegative;
    private ValidationString $string;
    private ValidationChoice $choice;
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator($this);
        $this->comparison = ValidationFactory::createValidationComparison();
        $this->datetime = ValidationFactory::createValidationDateTime();
        $this->file = ValidationFactory::createValidationFile();
        $this->general = ValidationFactory::createValidationGeneral();
        $this->positiveNegative = ValidationFactory::createValidationPositiveNegative();
        $this->string = ValidationFactory::createValidationString();
        $this->choice = ValidationFactory::createValidationChoice();
    }

    public function getValue(): mixed
    {
        return $this->validator->getValue();
    }

    public function setValue(mixed $value): ValidationInterface
    {
        return $this->validator->setValue($value);
    }

    public function setConstraint(ValidationConstraint $constraint): void
    {
        $this->validator->setConstraint($constraint);
    }

    public function validate(bool $removeConstraints = true): array
    {
        return $this->validator->validate($removeConstraints);
    }

    /**
     * @return VALIDATION_ERRORS[]
     */
    public function validateValueObject(ValueObjectValidationInterface $valueObject): array
    {
        return $this->validator->validateValueObject($valueObject);
    }

    /**
     * @param array<string, ValueObjectValidationInterface> $valueObject
     *
     * @return array<string, VALIDATION_ERRORS[]>
     */
    public function validateValueObjectArray(array $valueObjects): array
    {
        return $this->validator->validateValueObjectArray($valueObjects);
    }

    public function notBlank(): self
    {
        $this->validator->setConstraint($this->general->notBlank());

        return $this;
    }

    public function notNull(): self
    {
        $this->validator->setConstraint($this->general->notNull());

        return $this;
    }

    public function type(TYPES $type): self
    {
        $this->validator->setConstraint($this->general->type($type));

        return $this;
    }

    public function email(EMAIL_TYPES $mode): self
    {
        $this->validator->setConstraint($this->general->email($mode));

        return $this;
    }

    public function stringLength(int $length): self
    {
        $this->validator->setConstraint($this->string->stringLength($length));

        return $this;
    }

    public function stringMin(int $min): self
    {
        $this->validator->setConstraint($this->string->stringMin($min));

        return $this;
    }

    public function stringMax(int $max): self
    {
        $this->validator->setConstraint($this->string->stringMax($max));

        return $this;
    }

    public function stringRange(int $min, int $max): self
    {
        $this->validator->setConstraint($this->string->stringRange($min, $max));

        return $this;
    }

    public function uuId(array $versions = [4], bool $strict = true): self
    {
        $this->validator->setConstraint($this->string->uuId($versions, $strict));

        return $this;
    }

    public function regEx(string $pattern, bool $patternMatch = true): self
    {
        $this->validator->setConstraint($this->string->regEx($pattern, $patternMatch));

        return $this;
    }

    public function alphanumeric(): self
    {
        $this->validator->setConstraint($this->string->alphanumeric());

        return $this;
    }

    /**
     * @param PROTOCOLS[] $protocols
     */
    public function url(array $protocols = []): self
    {
        $this->validator->setConstraint($this->string->url($protocols));

        return $this;
    }

    public function language(): self
    {
        $this->validator->setConstraint($this->string->language());

        return $this;
    }

    public function equalTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->equalTo($value));

        return $this;
    }

    public function notEqualTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->notEqualTo($value));

        return $this;
    }

    public function identicalTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->identicalTo($value));

        return $this;
    }

    public function notIdenticalTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->notIdenticalTo($value));

        return $this;
    }

    public function lessThan(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->lessThan($value));

        return $this;
    }

    public function lessThanOrEqual(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->lessThanOrEqual($value));

        return $this;
    }

    public function greaterThan(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->greaterThan($value));

        return $this;
    }

    public function greaterThanOrEqual(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->greaterThanOrEqual($value));

        return $this;
    }

    public function range(int|\DateTime $min, int|\DateTime $max): self
    {
        $this->validator->setConstraint($this->comparison->range($min, $max));

        return $this;
    }

    public function unique(): self
    {
        $this->validator->setConstraint($this->general->unique());

        return $this;
    }

    public function positive(): self
    {
        $this->validator->setConstraint($this->positiveNegative->positive());

        return $this;
    }

    public function positiveOrZero(): self
    {
        $this->validator->setConstraint($this->positiveNegative->positiveOrZero());

        return $this;
    }

    public function negative(): self
    {
        $this->validator->setConstraint($this->positiveNegative->negative());

        return $this;
    }

    public function negativeOrZero(): self
    {
        $this->validator->setConstraint($this->positiveNegative->negativeOrZero());

        return $this;
    }

    public function date(): self
    {
        $this->validator->setConstraint($this->datetime->date());

        return $this;
    }

    public function dateTime(): self
    {
        $this->validator->setConstraint($this->datetime->dateTime());

        return $this;
    }

    public function time(): self
    {
        $this->validator->setConstraint($this->datetime->time());

        return $this;
    }

    /**
     * @param DateTimeZone|null $timeZone
     */
    public function timeZone(int|null $timeZone): self
    {
        $this->validator->setConstraint($this->datetime->timeZone($timeZone));

        return $this;
    }

    public function file(mixed $maxSize, bool|null $binaryFormat, array|string|null $mimeTypes): self
    {
        $this->validator->setConstraint($this->file->file($maxSize, $binaryFormat, $mimeTypes));

        return $this;
    }

    public function choice(array|null $choices, bool|null $multiple, bool|null $strict, int|null $min, int|null $max): self
    {
        $this->validator->setConstraint($this->choice->choice($choices, $multiple, $strict, $min, $max));

        return $this;
    }
}
