<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

class ValidationFactory
{
    public static function createValidationComparison(): ValidationComparison
    {
        return new ValidationComparison();
    }

    public static function createValidationDateTime(): ValidationDateTime
    {
        return new ValidationDateTime();
    }

    public static function createValidationFile(): ValidationFile
    {
        return new ValidationFile();
    }

    public static function createValidationGeneral(): ValidationGeneral
    {
        return new ValidationGeneral();
    }

    public static function createValidationPositiveNegative(): ValidationPositiveNegative
    {
        return new ValidationPositiveNegative();
    }

    public static function createValidationString(): ValidationString
    {
        return new ValidationString();
    }

    public static function createValidationChoice(): ValidationChoice
    {
        return new ValidationChoice();
    }
}
