<?php

declare(strict_types=1);

namespace Order\Application\OrderModify\Dto;

use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class OrderModifyInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $orderId;
    public readonly Identifier $groupId;
    public readonly Identifier $productId;
    public readonly IdentifierNullable $shopId;
    public readonly Description $description;
    public readonly Amount $amount;

    public function __construct(
        UserShared $userSession,
        string|null $orderId,
        string|null $groupId,
        string|null $productId,
        string|null $shopId,
        string|null $description,
        float|null $amount,
    ) {
        $this->userSession = $userSession;
        $this->orderId = ValueObjectFactory::createIdentifier($orderId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productId = ValueObjectFactory::createIdentifier($productId);
        $this->shopId = ValueObjectFactory::createIdentifierNullable($shopId);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->amount = ValueObjectFactory::createAmount($amount);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'order_id' => $this->orderId,
            'group_id' => $this->groupId,
            'product_id' => $this->productId,
            'shop_id' => $this->shopId,
            'description' => $this->description,
            'amount' => $this->amount,
        ]);
    }
}
