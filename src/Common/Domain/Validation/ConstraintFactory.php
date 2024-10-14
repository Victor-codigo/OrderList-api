<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

use Common\Domain\Validation\Common\CONSTRAINTS_NAMES;
use Common\Domain\Validation\Common\PROTOCOLS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\User\EMAIL_TYPES;

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
     * @param int[] $versions Uuid::V...
     */
    public static function uuId(?array $versions = null, bool $strict = true): ConstraintDto
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

    public static function alphanumericWithWhiteSpace(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::ALPHANUMERIC_WITH_WHITESPACE, null);
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

    public static function json(): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::JSON, null);
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

    public static function count(int $value): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::ITERABLE_EQUAL, [
            'value' => $value,
        ]);
    }

    public static function countRange(int $min, int $max): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::ITERABLE_RANGE, [
            'min' => $min,
            'max' => $max,
        ]);
    }

    public static function countDivisibleBy(int $dividibleBy): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::ITERABLE_DIVISIBLE_BY, [
            'divisibleBy' => $dividibleBy,
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

    public static function timeZone(?int $timeZone): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::TIMEZONE, [
            'timeZone' => $timeZone,
        ]);
    }

    /**
     * @param string[]|string|null $mimeTypes
     */
    public static function file(mixed $maxSize, array|string|null $mimeTypes): ConstraintDto
    {
        return new ConstraintDto(CONSTRAINTS_NAMES::FILE, [
            'maxSize' => $maxSize,
            'mimeTypes' => $mimeTypes,
        ]);
    }

    /**
     * @param string[]|string|null $mimeTypes
     */
    public static function image(
        mixed $maxSize,
        array|string|null $mimeTypes,
        ?int $filenameMaxLength = null,
        ?int $minWith = null,
        ?int $maxWith = null,
        ?int $minHeigh = null,
        ?int $maxHeigh = null,
        ?int $minPixels = null,
        ?int $maxPixels = null,
        ?float $minAspectRatio = null,
        ?float $maxAspectRatio = null,
        bool $allowLandscape = true,
        bool $allowPortrait = true,
        bool $allowSquareImage = true,
        bool $detectCorrupted = false,
    ): ConstraintDto {
        return new ConstraintDto(CONSTRAINTS_NAMES::FILE_IMAGE, [
            'maxSize' => $maxSize,
            'mimeTypes' => $mimeTypes,
            'filenameMaxLength' => $filenameMaxLength,
            'minWith' => $minWith,
            'maxWith' => $maxWith,
            'minHeigh' => $minHeigh,
            'maxHeigh' => $maxHeigh,
            'minPixels' => $minPixels,
            'maxPixels' => $maxPixels,
            'minAspectRatio' => $minAspectRatio,
            'maxAspectRatio' => $maxAspectRatio,
            'allowLandscape' => $allowLandscape,
            'allowPortrait' => $allowPortrait,
            'allowSquareImage' => $allowSquareImage,
            'detectCorrupted' => $detectCorrupted,
        ]);
    }

    /**
     * @param mixed[]|null $choices
     */
    public static function choice(?array $choices, ?bool $multiple, ?bool $strict, ?int $min, ?int $max): ConstraintDto
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
