<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

use Common\Adapter\Validation\Validations\ValidationConstraint;
use Common\Domain\Validation\Common\PROTOCOLS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\User\EMAIL_TYPES;

interface ValidationInterface
{
    public function getValue(): mixed;

    public function setValue(mixed $value): ValidationInterface;

    public function setConstraint(ValidationConstraint $constraint): void;

    public function validate(bool $removeConstraints = true): array;

    /**
     * @return VALIDATION_ERRORS[]
     */
    public function validateValueObject(ValueObjectValidationInterface $valueObject): array;

    /**
     * @return array<string, VALIDATION_ERRORS[]>
     */
    public function validateValueObjectArray(array $valueObjects): array;

    public function notBlank(): self;

    public function notNull(): self;

    public function type(TYPES $type): self;

    public function email(EMAIL_TYPES $mode): self;

    public function stringLength(int $length): self;

    public function stringMin(int $min): self;

    public function stringMax(int $max): self;

    public function stringRange(int $min, int $max): self;

    public function uuId(?array $versions = null, bool $strict = true): self;

    public function regEx(string $pattern, bool $patternMatch = true): self;

    public function alphanumeric(): self;

    public function alphanumericWithWhiteSpace(): self;

    /**
     * @param PROTOCOLS[] $protocols
     */
    public function url(array $protocols = []): self;

    public function language(): self;

    public function json(): self;

    public function equalTo(mixed $value): self;

    public function notEqualTo(mixed $value): self;

    public function identicalTo(mixed $value): self;

    public function notIdenticalTo(mixed $value): self;

    public function lessThan(int|\DateTime $value): self;

    public function lessThanOrEqual(int|\DateTime $value): self;

    public function greaterThan(int|\DateTime $value): self;

    public function greaterThanOrEqual(int|\DateTime $value): self;

    public function range(int|\DateTime $min, int|\DateTime $max): self;

    public function count(int $value): self;

    public function countRange(int $min, int $max): self;

    public function countDivisibleBy(int $divisibleBy): self;

    public function unique(): self;

    public function positive(): self;

    public function positiveOrZero(): self;

    public function negative(): self;

    public function negativeOrZero(): self;

    public function date(): self;

    public function dateTime(): self;

    public function time(): self;

    /**
     * @param DateTimeZone|null $timeZone
     */
    public function timeZone(?int $timeZone): self;

    public function file(mixed $maxSize, array|string|null $mimeTypes): self;

    public function image(mixed $maxSize, array|string|null $mimeTypes, ?int $minWith = null, ?int $maxWith = null, ?int $minHeigh = null, ?int $maxHeigh = null, ?int $minPixels = null, ?int $maxPixels = null, ?float $minAspectRatio = null, ?float $maxAspectRatio = null, bool $allowLandscape = true, bool $allowPortrait = true, bool $allowSquareImage = true, bool $detectCorrupted = false): self;

    public function choice(?array $choices, ?bool $multiple, ?bool $strict, ?int $min, ?int $max): self;
}
