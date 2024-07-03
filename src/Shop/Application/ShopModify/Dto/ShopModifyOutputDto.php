<?php

declare(strict_types=1);

namespace Shop\Application\ShopModify\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ShopModifyOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $shopId
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->shopId->getValue(),
        ];
    }
}
