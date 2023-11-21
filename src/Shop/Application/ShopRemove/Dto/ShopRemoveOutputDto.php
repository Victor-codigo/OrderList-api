<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ShopRemoveOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $shopsId
     */
    public function __construct(
        public readonly array $shopsId
    ) {
    }

    public function toArray(): array
    {
        $shopsIds = array_map(
            fn (Identifier $shopId) => $shopId->getValue(),
            $this->shopsId
        );

        return [
            'id' => $shopsIds,
        ];
    }
}
