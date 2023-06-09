<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveOrder\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersRemoveOrderInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $listOrdersId;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $ordersId;

    public function __construct(UserShared $userSession, string|null $listOrdersId, string|null $groupId, array|null $ordersId)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->ordersId = array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            $ordersId ?? []
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'list_orders_id' => $this->listOrdersId,
            'group_id' => $this->groupId,
        ]);

        $errorListOrderIdEmpty = $validator
            ->setValue($this->ordersId)
            ->notBlank()
            ->validate();

        if (!empty($errorListOrderIdEmpty)) {
            $errorListOrderIdEmpty = ['orders_id_empty' => $errorListOrderIdEmpty];
        }

        $errorListOrderId = $validator->validateValueObjectArray($this->ordersId);
        if (!empty($errorListOrderId)) {
            $errorListOrderId = ['orders_id' => $errorListOrderId];
        }

        return array_merge($errorList, $errorListOrderIdEmpty, $errorListOrderId);
    }
}
