<?php

declare(strict_types=1);

namespace Product\Application\ProductSetShopPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductSetShopPriceInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $productId;
    public readonly Identifier $shopId;
    public readonly Identifier $groupId;
    public readonly Money $price;

    public function __construct(UserShared $userSession, string|null $productId, string|null $shopId, string|null $groupId, float|null $price)
    {
        $this->userSession = $userSession;
        $this->productId = ValueObjectFactory::createIdentifier($productId);
        $this->shopId = ValueObjectFactory::createIdentifier($shopId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->price = ValueObjectFactory::createMoney($price);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'product_id' => $this->productId,
            'shop_id' => $this->shopId,
            'group_id' => $this->groupId,
            'price' => $this->price,
        ]);
    }
}
