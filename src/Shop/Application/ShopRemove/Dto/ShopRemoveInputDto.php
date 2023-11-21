<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ShopRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $shopId;
    public readonly Identifier $groupId;

    public function __construct(UserShared $userSession, string|null $groupId, string|null $shopId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->shopId = ValueObjectFactory::createIdentifier($shopId);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'shop_id' => $this->shopId,
        ]);
    }
}
