<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Common;

enum CONSTRAINTS_NAMES: string
{
    case NOT_BLANK = 'notBlank';
    case NOT_NULL = 'notNull';

    case TYPE = 'type';
    case EMAIL = 'email';

    case EQUAL_TO = 'equalTo';
    case NOT_EQUAL_TO = 'notEqualTo';
    case IDENTICAL_TO = 'identicalTo';
    case NOT_IDENTICAL_TO = 'notIdenticalTo';
    case LESS_THAN = 'lessThan';
    case LESS_THAN_OR_EQUAL = 'lessThanOrEqual';
    case GREATER_THAN = 'greaterThan';
    case GREATER_THAN_OR_EQUAL = 'greaterThanOrEqual';

    case RANGE = 'range';
    case UNIQUE = 'unique';
    case POSITIVE = 'positive';
    case POSITIVE_OR_ZERO = 'positiveOrZero';
    case NEGATIVE = 'negative';
    case NEGATIVE_OR_ZERO = 'negativeOrZero';

    case STRING_LENGTH = 'stringLength';
    case STRING_MIN = 'stringMin';
    case STRING_MAX = 'stringMax';
    case STRING_RANGE = 'stringRange';
    case UUID = 'uuId';

    case REGEX = 'regEx';
    case ALPHANUMERIC = 'alphanumeric';
    case ALPHANUMERIC_WITH_WHITESPACE = 'alphanumericWithWhiteSpace';

    case DATE = 'date';
    case DATETIME = 'dateTime';
    case TIME = 'time';
    case TIMEZONE = 'timeZone';

    case FILE = 'file';
    case FILE_IMAGE = 'file_image';

    case CHOICE = 'choice';

    case URL = 'url';

    case LANGUAGE = 'language';

    case JSON = 'json';

    case ITERABLE_EQUAL = 'iterableEqual';
    case ITERABLE_RANGE = 'iterableRange';
    case ITERABLE_DIVISIBLE_BY = 'iterableDivisibleBy';
}
