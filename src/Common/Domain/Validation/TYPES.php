<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

enum TYPES: string
{
    case ARRAY = 'array';
    case BOOL = 'bool';
    case CALLABLE = 'callable';
    case FLOAT = 'float';
    case DOUBLE = 'double';
    case INT = 'int';
    case INTEGER = 'integer';
    case ITERABLE = 'iterable';
    case LONG = 'long';
    case NULL = 'null';
    case NUMERIC = 'numeric';
    case OBJECT = 'object';
    case REAL = 'real';
    case RESOURCE = 'resource';
    case SCALAR = 'scalar';
    case STRING = 'string';
    case ALNUM = 'alnum';
    case ALPHA = 'alpha';
    case CNTRL = 'cntrl';
    case DIGIT = 'digit';
    case GRAPH = 'graph';
    case LOWER = 'lower';
    case PRINT = 'print';
    case PUNCT = 'punct';
    case UPPER = 'upper';
    case XDIGIT = 'xdigit';
}
