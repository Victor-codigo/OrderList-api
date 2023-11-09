<?php

declare(strict_types=1);

namespace Order\Application\OrderCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Common\Domain\Validation\ValidationInterface;

class OrderCreateInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;

    /**
     * @var OrderDataDto[]
     */
    public readonly array $ordersData;

    public function __construct(UserShared $userSession, string|null $groupId, array|null $ordersData)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);

        $this->ordersData = array_map(
            fn (array $orderData) => $this->createOrderDto($orderData, $userSession->getId()),
            $ordersData ?? []
        );
    }

    private function createOrderDto(array $orderData, Identifier $userSessionId): OrderDataDto
    {
        $orderDataUnit = null;
        if (null !== $orderData['unit']) {
            $orderDataUnit = UNIT_MEASURE_TYPE::tryFrom($orderData['unit']);
        }

        return new OrderDataDto(
            ValueObjectFactory::createIdentifier($orderData['product_id'] ?? null),
            ValueObjectFactory::createIdentifierNullable($orderData['shop_id'] ?? null),
            $userSessionId,
            ValueObjectFactory::createDescription($orderData['description'] ?? null),
            ValueObjectFactory::createAmount($orderData['amount'] ?? null),
            ValueObjectFactory::createUnit($orderDataUnit),
        );
    }

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
                'unit' => $order->unit,
            ]),
            $this->ordersData
        ));

        $errorListGroupId = $validator->validateValueObject($this->groupId);
        if (!empty($errorListGroupId)) {
            $errorListGroupId = ['group_id' => $errorListGroupId];
        }

        return array_merge($ordersDataArrayError, $errorListOrders, $errorListGroupId);
    }
}
