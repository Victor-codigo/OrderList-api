<?php

declare(strict_types=1);

namespace Order\Application\OrderModify\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class OrderModifyProductShopRepeatedException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static ($message, ['order_product_and_shop_repeated' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
