<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetPrice\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersGetPriceInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $listOrdersId;
    public readonly Identifier $groupId;

    public function __construct(UserShared $userSession, ?string $listOrdersId, ?string $groupId)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'list_orders_id' => $this->listOrdersId,
            'group_id' => $this->groupId,
        ]);
    }
}
