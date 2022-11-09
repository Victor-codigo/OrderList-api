<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

class ConstraintFactory
{
    public static function notBlank(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::NOT_BLANK, null);
    }

    public static function notNull(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::NOT_NULL, null);
    }

    public static function type(TYPES $type): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::TYPE, [
            'type' => $type,
        ]);
    }

    public static function email(EMAIL_TYPES $mode): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::EMAIL, [
            'mode' => $mode,
        ]);
    }

    public static function stringLength(int $length): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::STRING_LENGTH, [
            'length' => $length,
        ]);
    }

    public static function stringMin(int $min): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::STRING_MIN, [
            'min' => $min,
        ]);
    }

    public static function stringMax(int $max): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::STRING_MAX, [
            'max' => $max,
        ]);
    }

    public static function stringRange(int $min, int $max): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::STRING_RANGE, [
            'min' => $min,
            'max' => $max,
        ]);
    }

    /**
     * @param array $versions Uuid::V...
     */
    public static function uuId(array $versions = [4], bool $strict = true): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::UUID, [
            'versions' => $versions,
            'strict' => $strict,
        ]);
    }

    public static function regEx(string $pattern, bool $patternMatch = true): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::REGEX, [
            'pattern' => $pattern,
            'patternMatch' => $patternMatch,
        ]);
    }

    public static function alphanumeric(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::ALPHANUMERIC, null);
    }

    /**
     * @param PROTOCOLS[] $protocols
     */
    public static function url(array $protocols = []): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::URL, [
            'protocols' => $protocols,
        ]);
    }

    public static function language(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::LANGUAGE, null);
    }

    public static function equalTo(mixed $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::EQUAL_TO, [
            'value' => $value,
        ]);
    }

    public static function notEqualTo(mixed $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::NOT_EQUAL_TO, [
            'value' => $value,
        ]);
    }

    public static function identicalTo(mixed $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::IDENTICAL_TO, [
            'value' => $value,
        ]);
    }

    public static function notIdenticalTo(mixed $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::NOT_IDENTICAL_TO, [
            'value' => $value,
        ]);
    }

    public static function lessThan(int|\DateTime $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::LESS_THAN, [
            'value' => $value,
        ]);
    }

    public static function lessThanOrEqual(int|\DateTime $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::LESS_THAN_OR_EQUAL, [
            'value' => $value,
        ]);
    }

    public static function greaterThan(int|\DateTime $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::GREATER_THAN, [
            'value' => $value,
        ]);
    }

    public static function greaterThanOrEqual(int|\DateTime $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::GREATER_THAN_OR_EQUAL, [
            'value' => $value,
        ]);
    }

    public static function range(int|\DateTime $min, int|\DateTime $max): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::RANGE, [
            'min' => $min,
            'max' => $max,
        ]);
    }

    public static function unique(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::UNIQUE, null);
    }

    public static function positive(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::POSITIVE, null);
    }

    public static function positiveOrZero(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::POSITIVE_OR_ZERO, null);
    }

    public static function negative(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::NEGATIVE, null);
    }

    public static function negativeOrZero(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::NEGATIVE_OR_ZERO, null);
    }

    public static function date(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::DATE, null);
    }

    public static function dateTime(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::DATETIME, null);
    }

    public static function time(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::TIME, null);
    }

    public static function timeZone(int|null $timeZone): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::TIMEZONE, [
            'timeZone' => $timeZone,
        ]);
    }

    public static function file(mixed $maxSize, bool|null $binaryFormat, array|string|null $mimeTypes): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::FILE, [
            'maxSize' => $maxSize,
            'binaryFormat' => $binaryFormat,
            'mimeTypes' => $mimeTypes,
        ]);
    }

    public static function choice(array|null $choices, bool|null $multiple, bool|null $strict, int|null $min, int|null $max): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::CHOICE, [
            'choices' => $choices,
            'multiple' => $multiple,
            'strict' => $strict,
            'min' => $min,
            'max' => $max,
        ]);
    }
}
