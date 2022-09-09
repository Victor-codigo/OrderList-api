<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use DateTimeZone;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\Timezone;

class ValidationDateTime extends ValidationConstraintBase
{
    public function date(): ValidationConstraint
    {
        return $this->createConstraint(
            new Date(),
            [
                Date::INVALID_DATE_ERROR => VALIDATION_ERRORS::DATE_INVALID,
                Date::INVALID_FORMAT_ERROR => VALIDATION_ERRORS::DATE_INVALID_FORMAT,
            ]
        );
    }

    public function dateTime(): ValidationConstraint
    {
        return $this->createConstraint(
            new DateTime(),
            [
                DateTime::INVALID_DATE_ERROR => VALIDATION_ERRORS::DATETIME_INVALID_DATE,
                DateTime::INVALID_FORMAT_ERROR => VALIDATION_ERRORS::DATETIME_INVALID_FORMAT,
                DateTime::INVALID_TIME_ERROR => VALIDATION_ERRORS::DATETIME_INVALID_TIME,
            ],
        );
    }

    public function time(): ValidationConstraint
    {
        return $this->createConstraint(
            new Time(),
            [
                Time::INVALID_FORMAT_ERROR => VALIDATION_ERRORS::TIME_INVALID_FORMAT,
                Time::INVALID_TIME_ERROR => VALIDATION_ERRORS::TIME_INVALID_TIME,
            ]
        );
    }

    /**
     * @param DateTimeZone|null $timeZone
     */
    public function timeZone(int|null $timeZone): ValidationConstraint
    {
        return $this->createConstraint(
            new Timezone($timeZone),
            [
                Timezone::TIMEZONE_IDENTIFIER_ERROR => VALIDATION_ERRORS::TIMEZONE_IDENTIFIER,
                Timezone::TIMEZONE_IDENTIFIER_IN_COUNTRY_ERROR => VALIDATION_ERRORS::TIMEZONE_IDENTIFIER_IN_COUNTRY,
                Timezone::TIMEZONE_IDENTIFIER_IN_ZONE_ERROR => VALIDATION_ERRORS::TIMEZONE_IDENTIFIER_IN_ZONE,
                Timezone::TIMEZONE_IDENTIFIER_INTL_ERROR => VALIDATION_ERRORS::TIMEZONE_IDENTIFIER_IN_INTL,
            ]
        );
    }
}
