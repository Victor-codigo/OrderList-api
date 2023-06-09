<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveOrder\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class ListOrdersRemoveOrderOrdersNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['orders_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
