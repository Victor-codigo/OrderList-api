<?php

declare(strict_types=1);

namespace Common\Domain\Validation\UnitMeasure;

enum UNIT_MEASURE_TYPE: string
{
    case UNITS = 'UNITS';
    case KG = 'KG';
    case G = 'G';
    case M = 'M';
    case DM = 'DM';
    case CM = 'CM';
    case MM = 'MM';
}
