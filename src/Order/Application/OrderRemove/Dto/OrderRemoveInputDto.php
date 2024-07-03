<?php

declare(strict_types=1);

namespace Order\Application\OrderRemove\Dto;

use Override;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class OrderRemoveInputDto implements ServiceInputDtoInterface
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
        $this->ordersId = array_map(
            fn (string $orderId): Identifier => ValueObjectFactory::createIdentifier($orderId),
            $ordersId ?? []
        );

        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorListNoOrdersId = $validator
            ->setValue($this->ordersId)
            ->notBlank()
            ->validate();

        if (!empty($errorListNoOrdersId)) {
            $errorListNoOrdersId = ['orders_empty' => $errorListNoOrdersId];
        }

        $errorListOrdersId = $validator->validateValueObjectArray($this->ordersId);
        if (!empty($errorListOrdersId)) {
            $errorListOrdersId = ['orders_id' => $errorListOrdersId];
        }

        $errorListGroupId = $validator->validateValueObject($this->groupId);

        if (!empty($errorListGroupId)) {
            $errorListGroupId = ['group_id' => $errorListGroupId];
        }

        return array_merge($errorListNoOrdersId, $errorListOrdersId, $errorListGroupId);
    }
}
