<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemoveAllGroupsShops\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class ShopRemoveAllGroupsShopsOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $shopsId
     */
    public function __construct(
        public readonly array $shopsId,
    ) {
    }

    /**
     * @return array{ id: array<int, string> }
     */
    #[\Override]
    public function toArray(): array
    {
        $shopsIds = array_map(
            fn (Identifier $shopId): ?string => $shopId->getValue(),
            $this->shopsId
        );

        return [
            'id' => $shopsIds,
        ];
    }
}
