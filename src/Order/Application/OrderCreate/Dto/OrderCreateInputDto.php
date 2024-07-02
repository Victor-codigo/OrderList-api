<?php

declare(strict_types=1);

namespace Order\Application\OrderCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class OrderCreateInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly Identifier $listOrdersId;

    /**
     * @var OrderDataDto[]
     */
    public readonly array $ordersData;

    public function __construct(UserShared $userSession, ?string $groupId, ?string $listOrdersId, ?array $ordersData)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);

        $this->ordersData = array_map(
            fn (array $orderData) => $this->createOrderDto($orderData, $userSession->getId()),
            $ordersData ?? []
        );
    }

    private function createOrderDto(array $orderData, Identifier $userSessionId): OrderDataDto
    {
        return new OrderDataDto(
            ValueObjectFactory::createIdentifier($orderData['list_orders_id'] ?? null),
            ValueObjectFactory::createIdentifier($orderData['product_id'] ?? null),
            ValueObjectFactory::createIdentifierNullable($orderData['shop_id'] ?? null),
            $userSessionId,
            ValueObjectFactory::createDescription($orderData['description'] ?? null),
            ValueObjectFactory::createAmount($orderData['amount'] ?? null),
        );
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $ordersDataArrayError = $validator
                ->setValue($this->ordersData)
                ->notBlank()
                ->validate();

        if (!empty($ordersDataArrayError)) {
            $ordersDataArrayError = ['orders_empty' => $ordersDataArrayError];
        }

        $errorListOrders = array_filter(array_map(
            fn (OrderDataDto $order) => $validator->validateValueObjectArray([
                'product_id' => $order->productId,
                'shop_id' => $order->shopId,
                'description' => $order->description,
                'amount' => $order->amount,
            ]),
            $this->ordersData
        ));

        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'list_orders_id' => $this->listOrdersId,
        ]);

        return array_merge($ordersDataArrayError, $errorListOrders, $errorList);
    }
}
