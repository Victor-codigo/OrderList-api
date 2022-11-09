<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

enum VALIDATION_ERRORS
{
    case OK;

    case NOT_BLANK;
    case NOT_NULL;

    case EMAIL;

    case STRING_MIN;
    case STRING_MAX;
    case STRING_INVALID_CHARACTERS;
    case STRING_NOT_EQUAL_LENGTH;
    case STRING_TOO_LONG;
    case STRING_TOO_SHORT;

    case TYPE;

    case EQUAL_TO;
    case NOT_EQUAL_TO;

    case IDENTICAL_TO;
    case NOT_IDENTICAL_TO;

    case LESS_THAN;
    case LESS_THAN_OR_EQUAL;

    case GREATER_THAN;
    case GREATER_THAN_OR_EQUAL;

    case RANGE_TOO_LOW;
    case RANGE_TOO_HIGH;
    case RANGE_NOT_IN_RANGE;
    case RANGE_INVALID_CHARACTERS;

    case UNIQUE;

    case POSITIVE;
    case POSITIVE_OR_ZERO;

    case NEGATIVE;
    case NEGATIVE_OR_ZERO;

    case DATE_INVALID;
    case DATE_INVALID_FORMAT;

    case DATETIME_INVALID_DATE;
    case DATETIME_INVALID_FORMAT;
    case DATETIME_INVALID_TIME;

    case TIME_INVALID_FORMAT;
    case TIME_INVALID_TIME;

    case TIMEZONE_IDENTIFIER;
    case TIMEZONE_IDENTIFIER_IN_COUNTRY;
    case TIMEZONE_IDENTIFIER_IN_ZONE;
    case TIMEZONE_IDENTIFIER_IN_INTL;

    case FILE_INVALID_MIME_TYPE;
    case FILE_NOT_FOUND;
    case FILE_NOT_READABLE;
    case FILE_TOO_LARGE;
    case FILE_EMPTY;

    case CHOICE_NOT_SUCH;
    case CHOICE_TOO_FEW;
    case CHOICE_TOO_MUCH;

    case UUID_INVALID_CHARACTERS;
    case UUID_INVALID_HYPHEN_PLACEMENT;
    case UUID_INVALID_VARIANT;
    case UUID_INVALID_VERSION;
    case UUID_TOO_LONG;
    case UUID_TOO_SHORT;

    case REGEX_FAIL;

    case ALPHANUMERIC;

    case URL;

    case LANGUAGE;
}
