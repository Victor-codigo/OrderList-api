<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Filter;

enum FILTER_SECTION: string
{
    case PRODUCT = 'product';
    case SHOP = 'shop';
    case LIST_ORDERS = 'list_orders';
    case ORDER = 'order';
}
