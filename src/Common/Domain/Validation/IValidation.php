<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

use Common\Adapter\Validation\Validations\ValidationConstraint;
use DateTime;

interface IValidation
{
    public function getValue(): mixed;

    public function setValue(mixed $value): IValidation;

    public function setConstraint(ValidationConstraint $constraint): void;

    public function validate(): array;

    /**
     * @return VALIDATION_ERRORS[]
     */
    public function validateValueObject(IValueObjectValidation $valueObject): array;

    public function notBlank(): self;

    public function notNull(): self;

    public function type(TYPES $type): self;

    public function email(EMAIL_TYPES $mode): self;

    public function stringLength(int $length): self;

    public function stringMin(int $min): self;

    public function stringMax(int $max): self;

    public function stringRange(int $min, int $max): self;

    public function uuId(array $versions = [4], bool $strict = true): self;

    public function equalTo(mixed $value): self;

    public function notEqualTo(mixed $value): self;

    public function identicalTo(mixed $value): self;

    public function notIdenticalTo(mixed $value): self;

    public function lessThan(int|DateTime $value): self;

    public function lessThanOrEqual(int|DateTime $value): self;

    public function greaterThan(int|DateTime $value): self;

    public function greaterThanOrEqual(int|DateTime $value): self;

    public function range(int|DateTime $min, int|DateTime $max): self;

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
    public function timeZone(int|null $timeZone): self;

    public function file(mixed $maxSize, bool|null $binaryFormat, array|string|null $mimeTypes): self;

    public function choice(array|null $choices, bool|null $multiple, bool|null $strict, int|null $min, int|null $max): self;
}