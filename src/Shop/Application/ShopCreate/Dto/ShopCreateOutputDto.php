<?php

declare(strict_types=1);

namespace Shop\Application\ShopCreate\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ShopCreateOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $shopId
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->shopId->getValue(),
        ];
    }
}
