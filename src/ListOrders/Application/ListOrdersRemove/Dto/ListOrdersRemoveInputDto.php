<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $listOrdersId;
    public readonly Identifier $groupId;

    public function __construct(UserShared $userSession, string|null $listOrdersId, string|null $groupId)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'list_orders_id' => $this->listOrdersId,
            'group_id' => $this->groupId,
        ]);
    }
}
