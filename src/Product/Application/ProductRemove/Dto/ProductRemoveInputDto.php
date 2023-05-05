<?php

declare(strict_types=1);

namespace Product\Application\ProductRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $productId;
    public readonly Identifier $groupId;
    public readonly Identifier $shopId;

    public function __construct(UserShared $userSession, string|null $groupId, string|null $productId, string|null $shopId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productId = ValueObjectFactory::createIdentifier($productId);
        $this->shopId = ValueObjectFactory::createIdentifier($shopId);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'product_id' => $this->productId,
            'shop_id' => $this->shopId,
        ]);
    }
}
