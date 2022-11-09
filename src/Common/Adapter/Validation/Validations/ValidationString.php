<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\Constraints\Alphanumeric\Alphanumeric;
use Common\Domain\Validation\PROTOCOLS;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\Language;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Uuid;

class ValidationString extends ValidationConstraintBase
{
    public function stringLength(int $length): ValidationConstraint
    {
        return $this->createStringConstraint($length, null, null);
    }

    public function stringMin(int $min): ValidationConstraint
    {
        return $this->createStringConstraint(null, $min, null);
    }

    public function stringMax(int $max): ValidationConstraint
    {
        return $this->createStringConstraint(null, null, $max);
    }

    public function stringRange(int $min, int $max): ValidationConstraint
    {
        return $this->createStringConstraint(null, $min, $max);
    }

    /**
     * @param array $versions Uuid::V...
     */
    public function uuId(array $versions = [4], bool $strict = true): ValidationConstraint
    {
        return $this->createConstraint(
            new Uuid(null, null, $versions, $strict),
            [
                Uuid::INVALID_CHARACTERS_ERROR => VALIDATION_ERRORS::UUID_INVALID_CHARACTERS,
                Uuid::INVALID_HYPHEN_PLACEMENT_ERROR => VALIDATION_ERRORS::UUID_INVALID_HYPHEN_PLACEMENT,
                Uuid::INVALID_VARIANT_ERROR => VALIDATION_ERRORS::UUID_INVALID_VARIANT,
                Uuid::INVALID_VERSION_ERROR => VALIDATION_ERRORS::UUID_INVALID_VARIANT,
                Uuid::TOO_LONG_ERROR => VALIDATION_ERRORS::UUID_TOO_LONG,
                Uuid::TOO_SHORT_ERROR => VALIDATION_ERRORS::UUID_TOO_SHORT,
            ]
        );
    }

    public function regEx(string $pattern, bool $patternMatch = true): ValidationConstraint
    {
        return $this->createConstraint(
            new Regex($pattern, null, null, $patternMatch, null, null, null, []),
            [Regex::REGEX_FAILED_ERROR => VALIDATION_ERRORS::REGEX_FAIL]
        );
    }

    public function alphanumeric(): ValidationConstraint
    {
        return $this->createConstraint(
            new Alphanumeric(),
            [Alphanumeric::ALPHANUMERIC_FAILED_ERROR => VALIDATION_ERRORS::ALPHANUMERIC]
        );
    }

    /**
     * @param PROTOCOLS[] $protocols
     */
    public function url(array $protocols = []): ValidationConstraint
    {
        $relativeProtocol = true;
        $protocolsString = null;

        if (!empty($protocols)) {
            $relativeProtocol = false;
            $protocolsString = array_map(
                fn (PROTOCOLS $protocol) => $protocol->value,
                $protocols
            );
        }

        return $this->createConstraint(
            new Url(protocols: $protocolsString, relativeProtocol: $relativeProtocol),
            [Url::INVALID_URL_ERROR => VALIDATION_ERRORS::URL]
        );
    }

    public function language(): ValidationConstraint
    {
        return $this->createConstraint(
            new Language(),
            [Language::NO_SUCH_LANGUAGE_ERROR => VALIDATION_ERRORS::LANGUAGE]
        );
    }

    private function createStringConstraint(int|null $exactly, int|null $min, int|null $max): ValidationConstraint
    {
        return $this->createConstraint(
            new Length($exactly, $min, $max),
            [
                Length::INVALID_CHARACTERS_ERROR => VALIDATION_ERRORS::STRING_INVALID_CHARACTERS,
                Length::NOT_EQUAL_LENGTH_ERROR => VALIDATION_ERRORS::STRING_NOT_EQUAL_LENGTH,
                Length::TOO_LONG_ERROR => VALIDATION_ERRORS::STRING_TOO_LONG,
                Length::TOO_SHORT_ERROR => VALIDATION_ERRORS::STRING_TOO_SHORT,
            ]
        );
    }
}
