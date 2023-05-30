<?php

declare(strict_types=1);

namespace Order\Application\OrderGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class OrderGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $ordersId;
    public readonly Identifier $groupId;

    /**
     * @param string[]|null $ordersId
     */
    public function __construct(UserShared $userSession, array|null $ordersId, string|null $groupId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->ordersId = array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            $ordersId ?? []
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListOrdersIdEmpty = $validator
            ->setValue($this->ordersId)
            ->notBlank()
            ->validate();

        if (!empty($errorListOrdersIdEmpty)) {
            $errorListOrdersIdEmpty = ['orders_id_empty' => $errorListOrdersIdEmpty];
        }

        $errorListOrdersId = $validator->validateValueObjectArray($this->ordersId);
        if (!empty($errorListOrdersId)) {
            $errorListOrdersId = ['orders_id' => $errorListOrdersId];
        }

        $errorListGroupId = $validator->validateValueObjectArray(['group_id' => $this->groupId]);

        return array_merge($errorListOrdersIdEmpty, $errorListOrdersId, $errorListGroupId);
    }
}
