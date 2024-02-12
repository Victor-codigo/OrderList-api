<?php

declare(strict_types=1);

namespace Product\Application\GetProductShopPrice\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class GetProductShopPriceProductNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static ($message, ['products_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
