<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ListOrdersGetDataListOrderIdsAndListOrderNameStartsWithAreNullException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['list_orders_id_and_list_orders_name_starts_with_are_null' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
