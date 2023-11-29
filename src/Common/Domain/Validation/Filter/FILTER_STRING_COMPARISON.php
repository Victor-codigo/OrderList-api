<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Filter;

enum FILTER_STRING_COMPARISON: string
{
    case STARTS_WITH = 'starts_with';
    case ENDS_WITH = 'ends_with';
    case CONTAINS = 'contains';
    case EQUALS = 'equals';
}
