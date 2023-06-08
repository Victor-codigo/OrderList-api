<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersAddOrder\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersAddOrderInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $listOrdersId;
    public readonly Identifier $groupId;
    /**
     * @var OrderBoughtDto[]
     */
    public readonly array $ordersBought;

    public function __construct(UserShared $userSession, string|null $listOrdersId, string|null $groupId, array|null $orders)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->ordersBought = array_map(
            fn (array $order) => $this->createOrderBought($order),
            $orders ?? []
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'list_orders_id' => $this->listOrdersId,
            'group_id' => $this->groupId,
        ]);

        $errorListOrdersBought = $this->validateOrdersBought($validator);

        return array_merge($errorList, $errorListOrdersBought);
    }

    private function validateOrdersBought(ValidationInterface $validator)
    {
        $errorListOrdersIdEmpty = $validator
            ->setValue($this->ordersBought)
            ->notBlank()
            ->validate();
        if (!empty($errorListOrdersIdEmpty)) {
            return ['orders_id_empty' => $errorListOrdersIdEmpty];
        }

        $ordersId = array_map(
            fn (OrderBoughtDto $orderBought) => $orderBought->orderId,
            $this->ordersBought
        );

        $errorListOrders = $validator->validateValueObjectArray($ordersId);
        if (!empty($errorListOrders)) {
            return ['orders' => $errorListOrders];
        }

        return [];
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function createOrderBought(array $order): OrderBoughtDto
    {
        return new OrderBoughtDto(
            ValueObjectFactory::createIdentifier($order['order_id'] ?? null),
            $order['bought'] ?? false
        );
    }
}
