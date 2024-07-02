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
use Common\Adapter\Validation\Validations\ValidationIterable;
use Common\Adapter\Validation\Validations\ValidationPositiveNegative;
use Common\Adapter\Validation\Validations\ValidationString;
use Common\Domain\Validation\Common\PROTOCOLS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\User\EMAIL_TYPES;
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
    private ValidationIterable $iterable;
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
        $this->iterable = ValidationFactory::createValidationIterable();
        $this->choice = ValidationFactory::createValidationChoice();
    }

    #[\Override]
    public function getValue(): mixed
    {
        return $this->validator->getValue();
    }

    #[\Override]
    public function setValue(mixed $value): ValidationInterface
    {
        return $this->validator->setValue($value);
    }

    #[\Override]
    public function setConstraint(ValidationConstraint $constraint): void
    {
        $this->validator->setConstraint($constraint);
    }

    #[\Override]
    public function validate(bool $removeConstraints = true): array
    {
        return $this->validator->validate($removeConstraints);
    }

    /**
     * @return VALIDATION_ERRORS[]
     */
    #[\Override]
    public function validateValueObject(ValueObjectValidationInterface $valueObject): array
    {
        return $this->validator->validateValueObject($valueObject);
    }

    /**
     * @param array<string, ValueObjectValidationInterface> $valueObject
     *
     * @return array<string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validateValueObjectArray(array $valueObjects): array
    {
        return $this->validator->validateValueObjectArray($valueObjects);
    }

    #[\Override]
    public function notBlank(): self
    {
        $this->validator->setConstraint($this->general->notBlank());

        return $this;
    }

    #[\Override]
    public function notNull(): self
    {
        $this->validator->setConstraint($this->general->notNull());

        return $this;
    }

    #[\Override]
    public function type(TYPES $type): self
    {
        $this->validator->setConstraint($this->general->type($type));

        return $this;
    }

    #[\Override]
    public function email(EMAIL_TYPES $mode): self
    {
        $this->validator->setConstraint($this->general->email($mode));

        return $this;
    }

    #[\Override]
    public function stringLength(int $length): self
    {
        $this->validator->setConstraint($this->string->stringLength($length));

        return $this;
    }

    #[\Override]
    public function stringMin(int $min): self
    {
        $this->validator->setConstraint($this->string->stringMin($min));

        return $this;
    }

    #[\Override]
    public function stringMax(int $max): self
    {
        $this->validator->setConstraint($this->string->stringMax($max));

        return $this;
    }

    #[\Override]
    public function stringRange(int $min, int $max): self
    {
        $this->validator->setConstraint($this->string->stringRange($min, $max));

        return $this;
    }

    #[\Override]
    public function uuId(array $versions = null, bool $strict = true): self
    {
        $this->validator->setConstraint($this->string->uuId($versions, $strict));

        return $this;
    }

    #[\Override]
    public function regEx(string $pattern, bool $patternMatch = true): self
    {
        $this->validator->setConstraint($this->string->regEx($pattern, $patternMatch));

        return $this;
    }

    #[\Override]
    public function alphanumeric(): self
    {
        $this->validator->setConstraint($this->string->alphanumeric());

        return $this;
    }

    #[\Override]
    public function alphanumericWithWhiteSpace(): self
    {
        $this->validator->setConstraint($this->string->alphanumericWithWhiteSpace());

        return $this;
    }

    /**
     * @param PROTOCOLS[] $protocols
     */
    #[\Override]
    public function url(array $protocols = []): self
    {
        $this->validator->setConstraint($this->string->url($protocols));

        return $this;
    }

    #[\Override]
    public function language(): self
    {
        $this->validator->setConstraint($this->string->language());

        return $this;
    }

    #[\Override]
    public function json(): self
    {
        $this->validator->setConstraint($this->string->json());

        return $this;
    }

    #[\Override]
    public function equalTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->equalTo($value));

        return $this;
    }

    #[\Override]
    public function notEqualTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->notEqualTo($value));

        return $this;
    }

    #[\Override]
    public function identicalTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->identicalTo($value));

        return $this;
    }

    #[\Override]
    public function notIdenticalTo(mixed $value): self
    {
        $this->validator->setConstraint($this->comparison->notIdenticalTo($value));

        return $this;
    }

    #[\Override]
    public function lessThan(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->lessThan($value));

        return $this;
    }

    #[\Override]
    public function lessThanOrEqual(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->lessThanOrEqual($value));

        return $this;
    }

    #[\Override]
    public function greaterThan(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->greaterThan($value));

        return $this;
    }

    #[\Override]
    public function greaterThanOrEqual(int|\DateTime $value): self
    {
        $this->validator->setConstraint($this->comparison->greaterThanOrEqual($value));

        return $this;
    }

    #[\Override]
    public function range(int|\DateTime $min, int|\DateTime $max): self
    {
        $this->validator->setConstraint($this->comparison->range($min, $max));

        return $this;
    }

    #[\Override]
    public function count(int $value): self
    {
        $this->validator->setConstraint($this->iterable->count($value));

        return $this;
    }

    #[\Override]
    public function countRange(int $min, int $max): self
    {
        $this->validator->setConstraint($this->iterable->countRange($min, $max));

        return $this;
    }

    #[\Override]
    public function countDivisibleBy(int $divisibleBy): self
    {
        $this->validator->setConstraint($this->iterable->countDivisibleBy($divisibleBy));

        return $this;
    }

    #[\Override]
    public function unique(): self
    {
        $this->validator->setConstraint($this->general->unique());

        return $this;
    }

    #[\Override]
    public function positive(): self
    {
        $this->validator->setConstraint($this->positiveNegative->positive());

        return $this;
    }

    #[\Override]
    public function positiveOrZero(): self
    {
        $this->validator->setConstraint($this->positiveNegative->positiveOrZero());

        return $this;
    }

    #[\Override]
    public function negative(): self
    {
        $this->validator->setConstraint($this->positiveNegative->negative());

        return $this;
    }

    #[\Override]
    public function negativeOrZero(): self
    {
        $this->validator->setConstraint($this->positiveNegative->negativeOrZero());

        return $this;
    }

    #[\Override]
    public function date(): self
    {
        $this->validator->setConstraint($this->datetime->date());

        return $this;
    }

    #[\Override]
    public function dateTime(): self
    {
        $this->validator->setConstraint($this->datetime->dateTime());

        return $this;
    }

    #[\Override]
    public function time(): self
    {
        $this->validator->setConstraint($this->datetime->time());

        return $this;
    }

    /**
     * @param DateTimeZone|null $timeZone
     */
    #[\Override]
    public function timeZone(int|null $timeZone): self
    {
        $this->validator->setConstraint($this->datetime->timeZone($timeZone));

        return $this;
    }

    #[\Override]
    public function file(mixed $maxSize, array|string|null $mimeTypes): self
    {
        $this->validator->setConstraint($this->file->file($maxSize, $mimeTypes));

        return $this;
    }

    #[\Override]
    public function image(
        mixed $maxSize,
        array|string|null $mimeTypes,
        int|null $minWith = null,
        int|null $maxWith = null,
        int|null $minHeigh = null,
        int|null $maxHeigh = null,
        int|null $minPixels = null,
        int|null $maxPixels = null,
        float|null $minAspectRatio = null,
        float|null $maxAspectRatio = null,
        bool $allowLandscape = true,
        bool $allowPortrait = true,
        bool $allowSquareImage = true,
        bool $detectCorrupted = false
    ): self {
        $this->validator->setConstraint($this->file->image(
            $maxSize,
            $mimeTypes,
            $minWith,
            $maxWith,
            $minHeigh,
            $maxHeigh,
            $minPixels,
            $maxPixels,
            $minAspectRatio,
            $maxAspectRatio,
            $allowLandscape,
            $allowPortrait,
            $allowSquareImage,
            $detectCorrupted
        ));

        return $this;
    }

    #[\Override]
    public function choice(array|null $choices, bool|null $multiple, bool|null $strict, int|null $min, int|null $max): self
    {
        $this->validator->setConstraint($this->choice->choice($choices, $multiple, $strict, $min, $max));

        return $this;
    }
}
